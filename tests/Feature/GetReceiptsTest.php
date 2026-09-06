<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Dru1x\ExpoPush\ExpoPush;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushReceipt\PushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptIdCollection;
use Dru1x\ExpoPush\Request\GetReceiptsRequest;
use Dru1x\ExpoPush\Support\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class GetReceiptsTest extends TestCase
{
    protected MockClient $mockClient;
    protected ExpoPush $service;

    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        $this->mockClient = new MockClient();
        $this->service    = new ExpoPush();

        $this->service->withMockClient($this->mockClient);

        Config::preventStrayRequests();
    }

    #[Test]
    public function get_receipts_handles_push_receipt_id_collection(): void
    {
        $responseData = [
            'data' => [
                'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => ['status' => 'ok'],
                'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => ['status' => 'ok'],
                'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => ['status' => 'ok'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseData,
                headers: ['Content-Type' => 'application/json'],
            ),
        );

        $receiptIds = new PushReceiptIdCollection(
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(1, GetReceiptsRequest::class);

        $this->assertCount(3, $result->receipts);

        foreach ($result->receipts as $receipt) {
            $this->assertEquals(PushStatus::Ok, $receipt->status);
            $this->assertTrue($receiptIds->contains($receipt->id));
            ;
        }
    }

    #[Test]
    public function get_receipts_handles_push_receipt_id_array(): void
    {
        $responseData = [
            'data' => [
                'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => ['status' => 'ok'],
                'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => ['status' => 'ok'],
                'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => ['status' => 'ok'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseData,
                headers: ['Content-Type' => 'application/json'],
            ),
        );

        $receiptIds = [
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ];

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(1, GetReceiptsRequest::class);

        $this->assertCount(3, $result->receipts);

        foreach ($result->receipts as $receipt) {
            $this->assertEquals(PushStatus::Ok, $receipt->status);
            $this->assertTrue(in_array($receipt->id, $receiptIds));
        }
    }

    #[Test]
    public function get_receipts_does_not_send_requests_for_empty_collection(): void
    {
        $result = $this->service->getReceipts(new PushReceiptIdCollection());

        $this->mockClient->assertSentCount(0, GetReceiptsRequest::class);

        $this->assertCount(0, $result->receipts);
        $this->assertFalse($result->hasErrors());
    }

    #[Test]
    public function get_receipts_leaves_index_gaps_for_request_errors(): void
    {
        $receiptIds = $this->generatePushReceiptIds(10000);

        foreach ($receiptIds->chunk(1000) as $index => $receiptIdChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_RECEIPTS',
                                    'message' => 'You are trying to get more than 1000 push receipts in one request',
                                ],
                            ],
                        ],
                        status: 400,
                        headers: ['Content-Type' => 'application/json'],
                    ),
                );

                continue;
            }

            $responseBody = [
                'data' => array_combine(
                    $receiptIdChunk->toArray(),
                    array_fill(0, count($receiptIdChunk), ['status' => 'ok']),
                ),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json'],
                ),
            );
        }

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(10, GetReceiptsRequest::class);

        $this->assertCount(9000, $result->receipts);

        $this->assertInstanceOf(PushReceipt::class, $result->receipts->get(499));
        $this->assertNull($result->receipts->get(5000));
        $this->assertNull($result->receipts->get(5999));
        $this->assertInstanceOf(PushReceipt::class, $result->receipts->get(600));

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyReceipts, $result->errors->get(0)->code);
    }

    #[Test]
    public function get_receipts_reports_correct_end_index_for_partial_final_batch(): void
    {
        $receiptIds = $this->generatePushReceiptIds(10500);

        foreach ($receiptIds->chunk(1000) as $index => $receiptIdChunk) {

            if ($index === 10) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_RECEIPTS',
                                    'message' => 'You are trying to get more than 1000 push receipts in one request',
                                ],
                            ],
                        ],
                        status: 400,
                        headers: ['Content-Type' => 'application/json'],
                    ),
                );

                continue;
            }

            $responseBody = [
                'data' => array_combine(
                    $receiptIdChunk->toArray(),
                    array_fill(0, count($receiptIdChunk), ['status' => 'ok']),
                ),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json'],
                ),
            );
        }

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(11, GetReceiptsRequest::class);

        $this->assertCount(10000, $result->receipts);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);

        $error = $result->errors->get(0);
        $this->assertEquals(PushErrorCode::PushTooManyReceipts, $error->code);
        $this->assertEquals(10000, $error->startIndex);
        $this->assertEquals(10499, $error->endIndex);
    }

    #[Test]
    public function get_receipts_exposes_push_errors_for_each_request_error(): void
    {
        $receiptIds = $this->generatePushReceiptIds(10000);

        foreach ($receiptIds->chunk(1000) as $index => $receiptIdChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_RECEIPTS',
                                    'message' => 'You are trying to get more than 1000 push receipts in one request',
                                ],
                            ],
                        ],
                        status: 400,
                        headers: ['Content-Type' => 'application/json'],
                    ),
                );

                continue;
            }

            $responseBody = [
                'data' => array_combine(
                    $receiptIdChunk->toArray(),
                    array_fill(0, count($receiptIdChunk), ['status' => 'ok']),
                ),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json'],
                ),
            );
        }

        $result = $this->service->getReceipts($receiptIds);

        $this->mockClient->assertSentCount(10, GetReceiptsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyReceipts, $result->errors->get(0)->code);
    }

    // Helpers ----

    protected function generatePushReceiptIds(int $count): PushReceiptIdCollection
    {
        $receiptIds = [];

        for ($i = 0; $i < $count; $i++) {
            $receiptIds[] = $this->generatePushReceiptId();
        }

        return new PushReceiptIdCollection(...$receiptIds);
    }

    protected function generatePushReceiptId(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
