<?php

namespace Dru1x\ExpoPush\Request;

use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\PushMessageCollection;
use Dru1x\ExpoPush\PushTicket\FailedPushTicket;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\PushTicket\PushTicketDetails;
use Dru1x\ExpoPush\PushTicket\PushTicketErrorCode;
use Dru1x\ExpoPush\PushTicket\SuccessfulPushTicket;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;
use InvalidArgumentException;
use JsonException;
use OverflowException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;
use UnexpectedValueException;

final class SendNotificationsRequest extends Request implements HasBody
{
    use AcceptsJson;
    use HasJsonBody {
        body as protected buildBody;
    }
    use CompressesBody;

    public const MAX_NOTIFICATION_COUNT = 100;
    public const MAX_MESSAGE_DATA_BYTES = 4096;

    protected Method $method = Method::POST;

    public function __construct(protected readonly PushMessageCollection $pushMessages) {}

    public function resolveEndpoint(): string
    {
        return '/send';
    }

    public function body(): JsonBodyRepository
    {
        return $this->buildBody()->setJsonFlags(JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    // DTO ----

    /**
     * @inheritDoc
     *
     * @param Response $response
     *
     * @return PushTicketCollection
     * @throws UnexpectedValueException If the response body could not be decoded
     */
    public function createDtoFromResponse(Response $response): PushTicketCollection
    {
        try {
            $data = $response->json('data');
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                message: "Response could not be decoded: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        $tokens  = $this->pushMessages->getTokens();
        $tickets = new PushTicketCollection();

        foreach ($data as $index => $ticketData) {

            // Get the corresponding token and parse the ticket status
            $token  = $tokens->get($index);
            $status = PushStatus::from($ticketData['status']);

            // Instantiate and add a ticket to the collection based on the status
            $tickets->add(match ($status) {
                PushStatus::Ok    => $this->makeSuccessfulPushTicket($token, $ticketData),
                PushStatus::Error => $this->makeFailedPushTicket($token, $ticketData),
            });
        }

        return $tickets;
    }

    /**
     * Make a SuccessfulPushTicket from the given data
     *
     * @param PushToken         $token
     * @param array{id: string} $data
     *
     * @return SuccessfulPushTicket
     */
    protected function makeSuccessfulPushTicket(PushToken $token, array $data): SuccessfulPushTicket
    {
        return new SuccessfulPushTicket(
            token: $token,
            receiptId: $data['id'],
        );
    }

    /**
     * Make a FailedPushTicket from the given data
     *
     * @param PushToken                                                                       $token
     * @param array{message: string, details: ?array{error: ?string, expoPushToken: ?string}} $data
     *
     * @return FailedPushTicket
     */
    protected function makeFailedPushTicket(PushToken $token, array $data): FailedPushTicket
    {
        $detailsError = $data['details']['error'] ?? null;
        $detailsToken = $data['details']['expoPushToken'] ?? null;

        return new FailedPushTicket(
            token: $token,
            message: $data['message'],
            details: new PushTicketDetails(
                error: PushTicketErrorCode::tryFrom($detailsError) ?? PushTicketErrorCode::Unknown,
                expoPushToken: $detailsToken ? new PushToken($detailsToken) : null,
            ),
        );
    }

    // Internals ----

    /**
     * @return array<int, PushMessage>
     */
    protected function defaultBody(): array
    {
        $this->preventTooManyMessages();
        $this->preventTooMuchMessageData();

        return $this->pushMessages->all();
    }

    /**
     * Prevent the max number of notifications per request being exceeded
     *
     * @return void
     */
    protected function preventTooManyMessages(): void
    {
        if ($this->pushMessages->notificationCount() > self::MAX_NOTIFICATION_COUNT) {
            throw new OverflowException(
                "Cannot send more than " . self::MAX_NOTIFICATION_COUNT . " notifications per request",
            );
        }
    }

    /**
     * Prevent the data field of any message being larger than the limit
     *
     * @return void
     */
    protected function preventTooMuchMessageData(): void
    {
        foreach ($this->pushMessages as $pushMessage) {
            // If there's no data in the push message, there's nothing to do
            if (!$pushMessage->data) {
                continue;
            }

            try {
                // Calculate the size of the data field when converted to JSON
                $dataBytes = strlen(json_encode($pushMessage->data, JSON_THROW_ON_ERROR));
            } catch (JsonException $e) {
                throw new InvalidArgumentException(
                    "Push message data could not be encoded as JSON: {$e->getMessage()}",
                );
            }

            // Check the size of the included data
            if ($dataBytes > self::MAX_MESSAGE_DATA_BYTES) {
                throw new OverflowException(
                    "Individual push message data cannot be larger than " . self::MAX_MESSAGE_DATA_BYTES . " bytes",
                );
            }
        }
    }
}
