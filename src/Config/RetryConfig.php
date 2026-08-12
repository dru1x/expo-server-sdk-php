<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Config;

readonly class RetryConfig
{
    /**
     * @param int  $tries The maximum number of attempts to make for a single request
     * @param int  $retryInterval The base delay between retries, in milliseconds
     * @param bool $useExponentialBackoff Whether the delay should double after each attempt
     * @param bool $throwOnMaxTries Whether an exception should be thrown once retries are exhausted; if `false`,
     *                              the last error response is returned instead
     */
    public function __construct(
        public int  $tries = 3,
        public int  $retryInterval = 500,
        public bool $useExponentialBackoff = true,
        public bool $throwOnMaxTries = true,
    ) {}

    // Presets ----

    /**
     * Configure retries using default settings.
     *
     * Defaults: 3 tries, 500ms interval, exponential backoff.
     *
     * @return self
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Disable retries entirely (a single attempt, no delay).
     *
     * Failures still throw immediately, since `throwOnMaxTries` is unaffected and
     * remains `true` by default.
     *
     * @return self
     */
    public static function disabled(): self
    {
        return new self(
            tries: 1,
            retryInterval: 0,
            useExponentialBackoff: false,
        );
    }
}
