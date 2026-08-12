<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Request;

use Dru1x\ExpoPush\PushReceipt\FailedPushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptDetails;
use Dru1x\ExpoPush\PushReceipt\PushReceiptErrorCode;
use Dru1x\ExpoPush\PushReceipt\PushReceiptIdCollection;
use Dru1x\ExpoPush\PushReceipt\SuccessfulPushReceipt;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;
use JsonException;
use OverflowException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;
use UnexpectedValueException;

final class GetReceiptsRequest extends Request implements HasBody
{
    use AcceptsJson;
    use HasJsonBody;
    use CompressesBody;

    public const MAX_RECEIPT_COUNT = 1000;

    protected Method $method = Method::POST;

    public function __construct(protected readonly PushReceiptIdCollection $pushReceiptIds) {}

    public function resolveEndpoint(): string
    {
        return '/getReceipts';
    }

    // DTO ----

    /**
     * @inheritDoc
     *
     * @param Response $response
     *
     * @return PushReceiptCollection
     * @throws UnexpectedValueException If the response body could not be decoded
     */
    public function createDtoFromResponse(Response $response): PushReceiptCollection
    {
        try {
            $data = $response->json('data');
        } catch (JsonException $exception) {
            throw new UnexpectedValueException(
                message: "Response could not be decoded: {$exception->getMessage()}",
                previous: $exception,
            );
        }

        $receipts = new PushReceiptCollection();

        foreach ($data as $id => $receiptData) {

            // Parse the receipt status
            $status = PushStatus::from($receiptData['status']);

            $receipts->add(match ($status) {
                PushStatus::Ok    => $this->makeSuccessfulPushReceipt($id),
                PushStatus::Error => $this->makeFailedPushReceipt($id, $receiptData),
            });
        }

        return $receipts;
    }

    /**
     * Make a SuccessfulPushReceipt from the given receipt ID
     *
     * @param string $id
     *
     * @return SuccessfulPushReceipt
     */
    protected function makeSuccessfulPushReceipt(string $id): SuccessfulPushReceipt
    {
        return new SuccessfulPushReceipt(
            id: $id,
        );
    }

    /**
     * Make a FailedPushReceipt from the given data
     *
     * @param string $id
     * @param array{message: string, details: array{error?: string, expoPushToken?: string}} $data
     *
     * @return FailedPushReceipt
     */
    protected function makeFailedPushReceipt(string $id, array $data): FailedPushReceipt
    {
        $detailsError = $data['details']['error'] ?? null;
        $detailsToken = $data['details']['expoPushToken'] ?? null;

        return new FailedPushReceipt(
            id: $id,
            message: $data['message'],
            details: new PushReceiptDetails(
                error: PushReceiptErrorCode::tryFrom($detailsError) ?? PushReceiptErrorCode::Unknown,
                expoPushToken: $detailsToken ? new PushToken($detailsToken) : null,
            ),
        );
    }

    // Internals ----

    /**
     * @return array{ids: array<int, string>}
     */
    protected function defaultBody(): array
    {
        $this->preventTooManyReceipts();

        return [
            'ids' => $this->pushReceiptIds->all(),
        ];
    }

    /**
     * Prevent the max number of receipts per request being exceeded
     *
     * @return void
     */
    protected function preventTooManyReceipts(): void
    {
        if ($this->pushReceiptIds->count() > self::MAX_RECEIPT_COUNT) {
            throw new OverflowException(
                "Cannot send more than " . self::MAX_RECEIPT_COUNT . " push receipt IDs per request",
            );
        }
    }
}
