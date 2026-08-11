<?php

namespace Dru1x\ExpoPush\Config;

readonly class RetryConfig
{
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
