<?php

namespace Dru1x\ExpoPush\Config;

readonly class RetryConfig
{
    public function __construct(
        public ?int  $tries = null,
        public ?int  $retryInterval = null,
        public ?bool $useExponentialBackoff = null,
        public ?bool $throwOnMaxTries = null,
    ){}
}