<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Composer\InstalledVersions;
use Dru1x\ExpoPush\ExpoPush;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Saloon\RateLimitPlugin\Stores\MemoryStore;

class SetupTest extends TestCase
{
    #[Test]
    public function custom_rate_limit_store_is_passed_to_connector(): void
    {
        $store    = new MemoryStore();
        $expoPush = new ExpoPush(rateLimitStore: $store);

        $connector = (new ReflectionProperty(ExpoPush::class, 'connector'))->getValue($expoPush);

        $this->assertSame($store, $connector->rateLimitStore());
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
