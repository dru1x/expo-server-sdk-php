<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Dru1x\ExpoPush\ExpoPush;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\PushMessageCollection;
use Dru1x\ExpoPush\PushTicket\FailedPushTicket;
use Dru1x\ExpoPush\PushTicket\PushTicket;
use Dru1x\ExpoPush\PushTicket\PushTicketErrorCode;
use Dru1x\ExpoPush\PushTicket\SuccessfulPushTicket;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Request\SendNotificationsRequest;
use Dru1x\ExpoPush\Support\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class SendNotificationsTest extends TestCase
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
    public function send_notifications_handles_push_message_collection(): void
    {
        $responseBody = [
            'data' => [
                ['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'],
                ['status' => 'ok', 'id' => 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'],
                ['status' => 'ok', 'id' => 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseBody,
                headers: ['Content-Type' => 'application/json'],
            ),
        );

        $messages = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification'),
        );

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertCount(3, $result->tickets);
        $this->assertFalse($result->hasErrors());

        foreach ($result->tickets as $index => $ticket) {
            $this->assertInstanceOf(SuccessfulPushTicket::class, $ticket);
            $this->assertEquals(PushStatus::Ok, $ticket->status);
            $this->assertEquals($responseBody['data'][$index]['id'], $ticket->receiptId);
        }
    }

    #[Test]
    public function send_notifications_handles_large_push_message_collection(): void
    {
        $messages = $this->generatePushMessages(1000);

        foreach ($messages->chunk(100) as $messageChunk) {

            $responseBody = [
                'data' => array_map(fn(PushMessage $message) => [
                    'status' => 'ok',
                    'id'     => $this->generatePushReceiptId(),
                ], $messageChunk->toArray()),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json'],
                ),
            );
        }

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(1000, $result->tickets);
    }

    #[Test]
    public function send_notifications_handles_push_message_array(): void
    {
        $responseData = [
            'data' => [
                ['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'],
                ['status' => 'ok', 'id' => 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'],
                ['status' => 'ok', 'id' => 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseData,
                headers: ['Content-Type' => 'application/json'],
            ),
        );

        $messages = [
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification'),
        ];

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertCount(3, $result->tickets);
        $this->assertFalse($result->hasErrors());

        foreach ($result->tickets as $index => $ticket) {
            $this->assertInstanceOf(SuccessfulPushTicket::class, $ticket);
            $this->assertEquals(PushStatus::Ok, $ticket->status);
            $this->assertEquals($responseData['data'][$index]['id'], $ticket->receiptId);
        }
    }

    #[Test]
    public function send_notifications_handles_large_push_message_array(): void
    {
        $messages = $this->generatePushMessages(1000)->toArray();

        foreach (array_chunk($messages, 100) as $messageChunk) {

            $responseBody = [
                'data' => array_map(fn(PushMessage $message) => [
                    'status' => 'ok',
                    'id'     => $this->generatePushReceiptId(),
                ], $messageChunk),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json'],
                ),
            );
        }

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(1000, $result->tickets);
    }

    #[Test]
    public function send_notifications_does_not_send_requests_for_empty_collection(): void
    {
        $result = $this->service->sendNotifications(new PushMessageCollection());

        $this->mockClient->assertSentCount(0, SendNotificationsRequest::class);

        $this->assertCount(0, $result->tickets);
        $this->assertFalse($result->hasErrors());
    }

    #[Test]
    public function send_notification_handles_single_push_message(): void
    {
        $message = $this->generatePushMessage();

        $responseBody = [
            'data' => [
                [
                    'status' => 'ok',
                    'id'     => $this->generatePushReceiptId(),
                ],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(
                body: $responseBody,
                headers: ['Content-Type' => 'application/json'],
            ),
        );

        $result = $this->service->sendNotification($message);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertCount(1, $result->tickets);
    }

    #[Test]
    public function send_notifications_handles_mixed_successful_and_failed_tickets(): void
    {
        $responseBody = [
            'data' => [
                ['status' => 'ok',    'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'],
                ['status' => 'error', 'message' => 'The device cannot be reached', 'details' => ['error' => 'DeviceNotRegistered', 'expoPushToken' => 'ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]']],
                ['status' => 'ok',    'id' => 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
            ],
        ];

        $this->mockClient->addResponse(
            MockResponse::make(body: $responseBody, headers: ['Content-Type' => 'application/json']),
        );

        $messages = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]')),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]')),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]')),
        );

        $result = $this->service->sendNotifications($messages);

        $this->assertCount(3, $result->tickets);
        $this->assertFalse($result->hasErrors());
        $this->assertTrue($result->hasSuccessfulTickets());
        $this->assertTrue($result->hasFailedTickets());

        $this->assertInstanceOf(SuccessfulPushTicket::class, $result->tickets->get(0));
        $this->assertInstanceOf(FailedPushTicket::class, $result->tickets->get(1));
        $this->assertInstanceOf(SuccessfulPushTicket::class, $result->tickets->get(2));

        /** @var FailedPushTicket $failedTicket */
        $failedTicket = $result->tickets->get(1);
        $this->assertEquals(PushTicketErrorCode::DeviceNotRegistered, $failedTicket->details->error);
    }

    #[Test]
    public function send_notifications_leaves_index_gaps_for_request_errors(): void
    {
        $messages = $this->generatePushMessages(1000);

        foreach ($messages->chunk(100) as $index => $messageChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_EXPERIENCE_IDS',
                                    'message' => 'You are trying to send push notifications to different Expo experiences',
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
                'data' => array_map(fn(PushMessage $message) => [
                    'status' => 'ok',
                    'id'     => $this->generatePushReceiptId(),
                ], $messageChunk->toArray()),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json'],
                ),
            );
        }

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertCount(900, $result->tickets);

        $this->assertInstanceOf(PushTicket::class, $result->tickets->get(499));
        $this->assertNull($result->tickets->get(500));
        $this->assertNull($result->tickets->get(599));
        $this->assertInstanceOf(PushTicket::class, $result->tickets->get(600));

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyExperienceIds, $result->errors->get(0)->code);
    }

    #[Test]
    public function send_notifications_exposes_push_errors_for_each_request_error(): void
    {
        $messages = $this->generatePushMessages(1000);

        foreach ($messages->chunk(100) as $index => $messageChunk) {

            if ($index === 5) {
                $this->mockClient->addResponse(
                    MockResponse::make(
                        body: [
                            'errors' => [
                                [
                                    'code'    => 'PUSH_TOO_MANY_EXPERIENCE_IDS',
                                    'message' => 'You are trying to send push notifications to different Expo experiences',
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
                'data' => array_map(fn(PushMessage $message) => [
                    'status' => 'ok',
                    'id'     => $this->generatePushReceiptId(),
                ], $messageChunk->toArray()),
            ];

            $this->mockClient->addResponse(
                MockResponse::make(
                    body: $responseBody,
                    headers: ['Content-Type' => 'application/json'],
                ),
            );
        }

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(10, SendNotificationsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::PushTooManyExperienceIds, $result->errors->get(0)->code);
    }

    #[Test]
    public function send_notifications_exposes_unauthorized_push_error_for_invalid_credentials(): void
    {
        $this->mockClient->addResponse(
            MockResponse::make(
                body: [
                    'errors' => [
                        [
                            'code'    => 'UNAUTHORIZED',
                            'message' => 'Invalid credentials',
                        ],
                    ],
                ],
                status: 401,
                headers: ['Content-Type' => 'application/json'],
            ),
        );

        $messages = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification'),
        );

        $result = $this->service->sendNotifications($messages);

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::Unauthorized, $result->errors->get(0)->code);
        $this->assertCount(0, $result->tickets);
    }

    // Helpers ----

    protected function generatePushMessage(): PushMessage
    {
        $tokenValue = bin2hex(random_bytes(11));

        return new PushMessage(
            to: new PushToken("ExponentPushToken[$tokenValue]"),
            title: "Test Notification",
            body: "A simple test push notification",
        );
    }

    protected function generatePushMessages(int $count): PushMessageCollection
    {
        $messages = [];

        for ($i = 0; $i < $count; $i++) {
            $messages[] = $this->generatePushMessage();
        }

        return new PushMessageCollection(...$messages);
    }

    protected function generatePushReceiptId(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
