<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Composer\InstalledVersions;
use Dru1x\ExpoPush\Config\RetryConfig;
use Dru1x\ExpoPush\ExpoPush;
use Dru1x\ExpoPush\ExpoPushConnector;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

class ConfigurationTest extends TestCase
{
    #[Test]
    public function custom_rate_limit_store_is_passed_to_connector(): void
    {
        $store    = new MemoryStore();
        $expoPush = new ExpoPush(rateLimitStore: $store);

        /** @var ExpoPushConnector $connector */
        $connector = (new ReflectionProperty(ExpoPush::class, 'connector'))->getValue($expoPush);

        $this->assertSame($store, $connector->rateLimitStore());
    }

    #[Test]
    public function retry_config_is_passed_to_connector(): void
    {
        $config   = new RetryConfig(tries: 5, retryInterval: 250, useExponentialBackoff: false, throwOnMaxTries: false);
        $expoPush = new ExpoPush(retryConfig: $config);

        /** @var ExpoPushConnector $connector */
        $connector = (new ReflectionProperty(ExpoPush::class, 'connector'))->getValue($expoPush);

        $this->assertSame(5, $connector->tries);
        $this->assertSame(250, $connector->retryInterval);
        $this->assertFalse($connector->useExponentialBackoff);
        $this->assertFalse($connector->throwOnMaxTries);
    }

    #[Test]
    public function sdk_version_returns_correct_version(): void
    {
        $composer = json_decode(
            file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
        );

        $service = new ExpoPush();

        $this->assertSame(
            InstalledVersions::getPrettyVersion($composer->name),
            $service->sdkVersion(),
        );
    }
}
