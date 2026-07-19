<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Unit;

use Dru1x\ExpoPush\ExpoPushConnector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\RateLimitPlugin\Exceptions\LimitException;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

class ExpoPushConnectorTest extends TestCase
{
    #[Test]
    public function resolves_the_expo_push_api_base_url(): void
    {
        $connector = new ExpoPushConnector();

        $this->assertSame('https://exp.host/--/api/v2/push', $connector->resolveBaseUrl());
    }

    #[Test]
    public function applies_bearer_token_authentication_when_a_token_is_provided(): void
    {
        $connector = new ExpoPushConnector('my-token');

        $authenticator = $connector->getAuthenticator();

        $this->assertInstanceOf(TokenAuthenticator::class, $authenticator);
        $this->assertSame('my-token', $authenticator->token);
        $this->assertSame('Bearer', $authenticator->prefix);
    }

    #[Test]
    public function applies_no_authentication_when_no_token_is_provided(): void
    {
        $connector = new ExpoPushConnector();

        $this->assertNull($connector->getAuthenticator());
    }

    #[Test]
    public function default_headers_include_accept_encoding_and_user_agent(): void
    {
        $connector = new ExpoPushConnector();

        $this->assertSame('gzip, deflate', $connector->headers()->get('Accept-Encoding'));
        $this->assertStringStartsWith('expo-server-sdk-php/', (string) $connector->headers()->get('User-Agent'));
        $this->assertStringContainsString('(dru1x)', (string) $connector->headers()->get('User-Agent'));
    }

    #[Test]
    public function default_headers_do_not_leak_an_authorization_header_when_no_token_is_provided(): void
    {
        $connector = new ExpoPushConnector();

        $this->assertNull($connector->headers()->get('Authorization'));
    }

    #[Test]
    public function rate_limit_allows_six_requests_per_second_and_sleeps_when_exceeded(): void
    {
        $connector = new ExpoPushConnector();

        $limit = $this->findLimit($connector, 'expo-push-limit');

        $this->assertSame(6, $limit->getAllow());
        $this->assertSame(1, $limit->getReleaseInSeconds());
        $this->assertTrue($limit->getShouldSleep());
    }

    #[Test]
    public function default_rate_limit_store_is_memory_store_when_none_supplied(): void
    {
        $connector = new ExpoPushConnector();

        $this->assertInstanceOf(MemoryStore::class, $connector->rateLimitStore());
    }

    #[Test]
    public function custom_rate_limit_store_is_used_when_supplied(): void
    {
        $store     = new MemoryStore();
        $connector = new ExpoPushConnector(rateLimitStore: $store);

        $this->assertSame($store, $connector->rateLimitStore());
    }

    // Internals ----

    private function findLimit(ExpoPushConnector $connector, string $name): Limit
    {
        try {
            foreach ($connector->getLimits() as $limit) {
                if (str_ends_with($limit->getName(), ":{$name}")) {
                    return $limit;
                }
            }
        } catch (LimitException $e) {
            $this->fail($e->getMessage());
        }

        $this->fail("Limit \"$name\" not found.");
    }
}
