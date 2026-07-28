<?php

namespace Dru1x\ExpoPush\Tests\Unit\Config;

use Dru1x\ExpoPush\Config\RetryConfig;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RetryConfigTest extends TestCase
{
    #[Test]
    public function constructor_defaults_to_three_tries_with_500ms_exponential_backoff(): void
    {
        $config = new RetryConfig();

        $this->assertSame(3, $config->tries);
        $this->assertSame(500, $config->retryInterval);
        $this->assertTrue($config->useExponentialBackoff);
        $this->assertTrue($config->throwOnMaxTries);
    }

    #[Test]
    public function constructor_arguments_are_applied_as_given(): void
    {
        $config = new RetryConfig(
            tries: 5,
            retryInterval: 1000,
            useExponentialBackoff: false,
            throwOnMaxTries: false,
        );

        $this->assertSame(5, $config->tries);
        $this->assertSame(1000, $config->retryInterval);
        $this->assertFalse($config->useExponentialBackoff);
        $this->assertFalse($config->throwOnMaxTries);
    }

    #[Test]
    public function default_preset_matches_the_constructor_defaults(): void
    {
        $config = RetryConfig::default();

        $this->assertSame(3, $config->tries);
        $this->assertSame(500, $config->retryInterval);
        $this->assertTrue($config->useExponentialBackoff);
        $this->assertTrue($config->throwOnMaxTries);
    }

    #[Test]
    public function disabled_preset_allows_a_single_attempt_with_no_delay(): void
    {
        $config = RetryConfig::disabled();

        $this->assertSame(1, $config->tries);
        $this->assertSame(0, $config->retryInterval);
        $this->assertFalse($config->useExponentialBackoff);

        // throwOnMaxTries is left at its default, so the single attempt still throws on failure
        $this->assertTrue($config->throwOnMaxTries);
    }
}
