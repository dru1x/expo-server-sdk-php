<?php

/** @noinspection PhpUnhandledExceptionInspection */

namespace Dru1x\ExpoPush\Tests\Unit;

use Composer\InstalledVersions;
use Dru1x\ExpoPush\Config\RetryConfig;
use Dru1x\ExpoPush\ExpoPushConnector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\RateLimitPlugin\Exceptions\LimitException;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

class ExpoPushConnectorTest extends TestCase
{
    private ?bool $originalSdkVersionResolved = null;
    private ?string $originalSdkVersion       = null;

    /** @var list<string> */
    private array $tempFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalSdkVersionResolved = (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersionResolved'))->getValue();
        $this->originalSdkVersion         = (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersion'))->getValue();
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersionResolved'))->setValue(null, $this->originalSdkVersionResolved);
        (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersion'))->setValue(null, $this->originalSdkVersion);

        foreach ($this->tempFiles as $tempFile) {
            is_file($tempFile) && unlink($tempFile);
        }

        $this->tempFiles = [];

        parent::tearDown();
    }

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

        $limit = $this->findLimit($connector, 'push-limit');

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

    #[Test]
    public function retry_configuration_defaults_to_three_tries_with_500ms_exponential_backoff(): void
    {
        $connector = new ExpoPushConnector();

        $this->assertSame(3, $connector->tries);
        $this->assertSame(500, $connector->retryInterval);
        $this->assertTrue($connector->useExponentialBackoff);
        $this->assertTrue($connector->throwOnMaxTries);
    }

    #[Test]
    public function custom_retry_configuration_is_used_when_supplied(): void
    {
        $retryConfig = new RetryConfig(tries: 5, retryInterval: 1000, useExponentialBackoff: false, throwOnMaxTries: false);
        $connector = new ExpoPushConnector(retryConfig: $retryConfig);

        $this->assertSame(5, $connector->tries);
        $this->assertSame(1000, $connector->retryInterval);
        $this->assertFalse($connector->useExponentialBackoff);
        $this->assertFalse($connector->throwOnMaxTries);
    }

    // SDK Version ----

    #[Test]
    public function sdk_version_resolves_the_real_installed_version(): void
    {
        $composerJsonPath = dirname(__DIR__, 2) . '/composer.json';
        $composer         = json_decode(file_get_contents($composerJsonPath));

        $this->assertSame(
            InstalledVersions::getPrettyVersion($composer->name),
            $this->resolveSdkVersion($composerJsonPath),
        );
    }

    #[Test]
    public function sdk_version_falls_back_to_unknown_when_composer_json_is_missing(): void
    {
        $this->assertSame('unknown', $this->resolveSdkVersion('/nonexistent/composer.json'));
    }

    #[Test]
    public function sdk_version_falls_back_to_unknown_when_composer_json_is_malformed(): void
    {
        $path = $this->writeTempFile('{not valid json');

        $this->assertSame('unknown', $this->resolveSdkVersion($path));
    }

    #[Test]
    public function sdk_version_falls_back_to_unknown_when_the_package_is_not_installed(): void
    {
        $path = $this->writeTempFile(json_encode(['name' => 'not/a-real-package']));

        $this->assertSame('unknown', $this->resolveSdkVersion($path));
    }

    #[Test]
    public function sdk_version_does_not_recompute_once_resolved(): void
    {
        (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersion'))->setValue(null, 'cached-test-value');
        (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersionResolved'))->setValue(null, true);

        $connector = new ExpoPushConnector();

        $this->assertSame('cached-test-value', $connector->sdkVersion());
    }

    #[Test]
    public function sdk_version_caches_a_falsy_resolved_value_instead_of_recomputing_it(): void
    {
        (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersion'))->setValue(null, '');
        (new ReflectionProperty(ExpoPushConnector::class, 'sdkVersionResolved'))->setValue(null, true);

        $connector = new ExpoPushConnector();

        $this->assertSame('', $connector->sdkVersion());
    }

    // Internals ----

    private function resolveSdkVersion(string $composerJsonPath): string
    {
        return (new ReflectionMethod(ExpoPushConnector::class, 'resolveSdkVersion'))
            ->invoke(null, $composerJsonPath);
    }

    private function writeTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'expo-push-sdk-version-test-');

        file_put_contents($path, $contents);

        $this->tempFiles[] = $path;

        return $path;
    }

    private function findLimit(ExpoPushConnector $connector, string $name): Limit
    {
        try {
            foreach ($connector->getLimits() as $limit) {
                if (str_ends_with($limit->getName(), ":$name")) {
                    return $limit;
                }
            }
        } catch (LimitException $e) {
            $this->fail($e->getMessage());
        }

        $this->fail("Limit \"$name\" not found.");
    }
}
