<?php

namespace Dru1x\ExpoPush\Support;

use Dru1x\ExpoPush\PushError\PushErrorCollection;

abstract readonly class Result
{
    public function __construct(public PushErrorCollection $errors) {}

    // Helpers ----

    public function hasErrors(): bool
    {
        return $this->errors->isNotEmpty();
    }
}
