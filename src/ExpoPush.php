<?php

namespace Dru1x\ExpoPush;

use Dru1x\ExpoPush\Config\RetryConfig;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\PushMessageCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptIdCollection;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\Request\GetReceiptsRequest;
use Dru1x\ExpoPush\Request\RequestExceptionHandler;
use Dru1x\ExpoPush\Request\SendNotificationsRequest;
use Dru1x\ExpoPush\Result\GetReceiptsResult;
use Dru1x\ExpoPush\Result\SendNotificationsResult;
use Generator;
use Saloon\Exceptions\InvalidPoolItemException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Pool;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;

class ExpoPush
{
    protected ExpoPushConnector $connector;

    public function __construct(
        ?string $authToken = null,
        ?RateLimitStore $rateLimitStore = null,
        ?RetryConfig $retryConfig = null,
    ) {
        $this->connector = new ExpoPushConnector($authToken, $rateLimitStore, $retryConfig);
    }

    // Interface ----

    /**
     * Send a set of push notifications
     *
     * This supports request concurrency, with up to 6 requests being sent at once
     *
     * @param PushMessageCollection|array<PushMessage> $pushMessages
     *
     * @return SendNotificationsResult
     * @throws InvalidPoolItemException
     */
    public function sendNotifications(PushMessageCollection|array $pushMessages): SendNotificationsResult
    {
        // Ensure push messages are in a collection
        if (is_array($pushMessages)) {
            $pushMessages = new PushMessageCollection(...$pushMessages);
        }

        // Prepare ticket and error collections
        $tickets = new PushTicketCollection();
        $errors  = new PushErrorCollection();

        // Prepare a new request pool
        $batchSize = SendNotificationsRequest::MAX_NOTIFICATION_COUNT;
        $pool      = $this->makeRequestPool($batchSize, $errors);

        // Split the message collection into a set of requests and add them to the pool
        $pool->setRequests(function () use ($batchSize, $pushMessages): Generator {
            $chunks = $pushMessages->chunkByNotifications($batchSize);

            foreach ($chunks as $pushMessages) {
                yield new SendNotificationsRequest($pushMessages);
            }
        });

        // When a response is received...
        $pool->withResponseHandler(function (Response $response, int $requestIndex) use ($batchSize, $tickets): void {
            $batch  = $response->dtoOrFail();
            $offset = $requestIndex * $batchSize;

            foreach ($batch as $index => $ticket) {
                $tickets->set($offset + $index, $ticket);
            }
        });

        // Send all the requests
        $pool->send()->wait();

        // Merge all the chunks of push tickets into a single ordered collection and return
        return new SendNotificationsResult($tickets, $errors);
    }

    /**
     * Send a single push notification
     *
     * @param PushMessage $pushMessage
     *
     * @return SendNotificationsResult
     * @throws InvalidPoolItemException
     */
    public function sendNotification(PushMessage $pushMessage): SendNotificationsResult
    {
        return $this->sendNotifications([$pushMessage]);
    }

    /**
     * Get available push receipts with the given IDs
     *
     * This supports request concurrency, with up to 6 requests being sent at once
     *
     * @param PushReceiptIdCollection|array<string> $receiptIds
     *
     * @return GetReceiptsResult
     * @throws InvalidPoolItemException
     */
    public function getReceipts(PushReceiptIdCollection|array $receiptIds): GetReceiptsResult
    {
        // Ensure receipt IDs are in a collection
        if (is_array($receiptIds)) {
            $receiptIds = new PushReceiptIdCollection(...$receiptIds);
        }

        // Prepare receipt and error collections
        $receipts = new PushReceiptCollection();
        $errors   = new PushErrorCollection();

        // Prepare a new request pool
        $batchSize = GetReceiptsRequest::MAX_RECEIPT_COUNT;
        $pool      = $this->makeRequestPool($batchSize, $errors);

        // Split the receipt ID collection into a set of requests and add them to the pool
        $pool->setRequests(function () use ($batchSize, $receiptIds): Generator {
            $chunks = $receiptIds->chunk($batchSize);

            foreach ($chunks as $chunk) {
                yield new GetReceiptsRequest($chunk);
            }
        });

        // When a response is received...
        $pool->withResponseHandler(function (Response $response, int $requestIndex) use ($batchSize, $receipts): void {
            $batch  = $response->dtoOrFail();
            $offset = $requestIndex * $batchSize;

            foreach ($batch as $index => $receipt) {
                $receipts->set($offset + $index, $receipt);
            }
        });

        // Send all the requests
        $pool->send()->wait();

        // Merge all the chunks of push receipts into a single collection and return
        return new GetReceiptsResult($receipts, $errors);
    }

    /**
     * Get the installed version of this SDK
     *
     * @return string
     */
    public function sdkVersion(): string
    {
        return $this->connector->sdkVersion();
    }

    // Internals ----

    /**
     * Use a mock client instead of a real one.
     *
     * This should only be used for testing.
     *
     * @internal
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): self
    {
        $this->connector = $this->connector->withMockClient($mockClient);

        return $this;
    }

    /**
     * Make a request pool with a concurrency limit and error handler
     *
     * @param int                 $batchSize The max number of elements to be sent per request
     * @param PushErrorCollection $errors A collection in which to store any push errors
     *
     * @return Pool
     */
    protected function makeRequestPool(int $batchSize, PushErrorCollection $errors): Pool
    {
        // Make a new pool with a concurrency limit and exception handler
        return $this->connector->pool(
            concurrency: $this->connector::MAX_CONCURRENT_REQUESTS,
            exceptionHandler: new RequestExceptionHandler($batchSize, $errors),
        );
    }
}
