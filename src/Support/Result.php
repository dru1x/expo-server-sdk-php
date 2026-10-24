<?php

namespace Dru1x\ExpoPush\Support;

use Dru1x\ExpoPush\PushError\PushErrorList;

abstract readonly class Result
{
    public function __construct(public PushErrorList $errors) {}

    // Helpers ----

    public function hasErrors(): bool
    {
        return $this->errors->isNotEmpty();
    }
}
