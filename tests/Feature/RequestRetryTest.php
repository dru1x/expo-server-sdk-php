<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Dru1x\ExpoPush\Config\RetryConfig;
use Dru1x\ExpoPush\ExpoPush;
use Dru1x\ExpoPush\ExpoPushConnector;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\PushMessageCollection;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Request\SendNotificationsRequest;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Config;
use Saloon\Contracts\Sender;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
use Saloon\Http\Senders\GuzzleSender;

class RequestRetryTest extends TestCase
{
    protected MockClient $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        MockClient::destroyGlobal();

        $this->mockClient = new MockClient();

        Config::preventStrayRequests();
    }

    #[Test]
    public function transient_server_failures_are_retried_and_eventually_succeed(): void
    {
        $retryConfig = new RetryConfig(tries: 2, retryInterval: 1);

        $service = new ExpoPush(retryConfig: $retryConfig);
        $service->withMockClient($this->mockClient);

        $this->mockClient->addResponses([
            MockResponse::make(status: 500),
            MockResponse::make(
                body: ['data' => [['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX']]],
                headers: ['Content-Type' => 'application/json'],
            ),
        ]);

        $result = $service->sendNotification($this->makeMessage());

        $this->mockClient->assertSentCount(2, SendNotificationsRequest::class);

        $this->assertFalse($result->hasErrors());
        $this->assertCount(1, $result->tickets);
    }

    #[Test]
    public function fatal_connection_errors_are_retried_and_eventually_succeed(): void
    {
        $retryConfig = new RetryConfig(tries: 2, retryInterval: 1);

        $service = new ExpoPush(retryConfig: $retryConfig);
        $service->withMockClient($this->mockClient);

        $this->mockClient->addResponses([
            MockResponse::make()->throw(
                fn(PendingRequest $pendingRequest) => new FatalRequestException(new RuntimeException('Connection refused'), $pendingRequest),
            ),
            MockResponse::make(
                body: ['data' => [['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX']]],
                headers: ['Content-Type' => 'application/json'],
            ),
        ]);

        $result = $service->sendNotification($this->makeMessage());

        $this->mockClient->assertSentCount(2, SendNotificationsRequest::class);

        $this->assertFalse($result->hasErrors());
        $this->assertCount(1, $result->tickets);
    }

    #[Test]
    public function exhausting_all_retries_still_results_in_a_push_error(): void
    {
        $retryConfig = new RetryConfig(tries: 2, retryInterval: 1);

        $service = new ExpoPush(retryConfig: $retryConfig);
        $service->withMockClient($this->mockClient);

        $this->mockClient->addResponses([
            MockResponse::make(status: 500),
            MockResponse::make(status: 500),
        ]);

        $result = $service->sendNotification($this->makeMessage());

        $this->mockClient->assertSentCount(2, SendNotificationsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::Failed, $result->errors->get(0)->code);
    }

    #[Test]
    public function client_errors_are_not_retried(): void
    {
        $retryConfig = new RetryConfig(tries: 3, retryInterval: 1);

        $service = new ExpoPush(retryConfig: $retryConfig);
        $service->withMockClient($this->mockClient);

        $this->mockClient->addResponse(
            MockResponse::make(
                body: ['errors' => [['code' => 'PUSH_TOO_MANY_EXPERIENCE_IDS', 'message' => 'Too many experience IDs']]],
                status: 400,
                headers: ['Content-Type' => 'application/json'],
            ),
        );

        $result = $service->sendNotification($this->makeMessage());

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertEquals(PushErrorCode::PushTooManyExperienceIds, $result->errors->get(0)->code);
    }

    #[Test]
    public function server_failures_are_retried_up_to_three_times_by_default(): void
    {
        $service = new ExpoPush();
        $service->withMockClient($this->mockClient);

        $this->mockClient->addResponses([
            MockResponse::make(status: 500),
            MockResponse::make(status: 500),
            MockResponse::make(status: 500),
        ]);

        $result = $service->sendNotification($this->makeMessage());

        $this->mockClient->assertSentCount(3, SendNotificationsRequest::class);

        $this->assertTrue($result->hasErrors());
        $this->assertCount(1, $result->errors);
        $this->assertEquals(PushErrorCode::Failed, $result->errors->get(0)->code);
    }

    #[Test]
    public function handle_retry_hook_can_prevent_further_retries(): void
    {
        $retryConfig = new RetryConfig(tries: 3, retryInterval: 1);

        $this->mockClient->addResponses([
            MockResponse::make(status: 500),
            MockResponse::make(status: 500),
        ]);

        $connector = new class (retryConfig: $retryConfig) extends ExpoPushConnector {
            public int $handleRetryCalls = 0;

            public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
            {
                $this->handleRetryCalls++;

                return false;
            }
        };

        $connector->withMockClient($this->mockClient);

        try {
            $connector->sendAsync(new SendNotificationsRequest(new PushMessageCollection($this->makeMessage())), $this->mockClient)->wait();
            $this->fail('Expected a RequestException to be thrown.');
        } catch (RequestException) {
            // Expected - handleRetry() vetoed the retry
        }

        $this->mockClient->assertSentCount(1, SendNotificationsRequest::class);
        $this->assertSame(1, $connector->handleRetryCalls);
    }

    #[Test]
    public function retries_carry_the_computed_delay_as_a_guzzle_request_option(): void
    {
        // This test sends through a mocked Guzzle handler rather than a Saloon MockClient, so
        // undo the stray-request prevention enabled in setUp() for the duration of this test
        Config::allowStrayRequests();

        $recordingSender = new RecordingSender([
            new GuzzleResponse(status: 500),
            new GuzzleResponse(status: 500),
            new GuzzleResponse(
                status: 200,
                headers: ['Content-Type' => 'application/json'],
                body: json_encode(['data' => [['status' => 'ok', 'id' => 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX']]]),
            ),
        ]);

        $retryConfig = new RetryConfig(tries: 3, retryInterval: 2, useExponentialBackoff: true);

        $connector = new class ($recordingSender, retryConfig: $retryConfig) extends ExpoPushConnector {
            public function __construct(protected Sender $testSender, RetryConfig $retryConfig)
            {
                parent::__construct(retryConfig: $retryConfig);
            }

            public function sender(): Sender
            {
                return $this->testSender;
            }
        };

        $request = new SendNotificationsRequest(new PushMessageCollection($this->makeMessage()));

        $connector->sendAsync($request)->wait();

        // Attempt 1 is sent with no delay; attempts 2 and 3 carry the exponentially-increasing backoff
        $this->assertSame([null, 2, 4], $recordingSender->recordedDelays);
    }

    // Helpers ----

    protected function makeMessage(): PushMessage
    {
        return new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification');
    }
}

// Helper Classes ----

class RecordingSender extends GuzzleSender
{
    public array $recordedDelays = [];

    /**
     * @param array<int, GuzzleResponse> $mockResponses
     */
    public function __construct(protected array $mockResponses)
    {
        parent::__construct();
    }

    protected function defaultHandlerStack(): HandlerStack
    {
        return HandlerStack::create(new MockHandler($this->mockResponses));
    }

    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface
    {
        $this->recordedDelays[] = $pendingRequest->config()->get('delay');

        return parent::sendAsync($pendingRequest);
    }
}
