<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Support;

use Dru1x\ExpoPush\PushError\PushErrorCollection;

abstract readonly class Result
{
    /**
     * @param PushErrorCollection $errors Any request-level errors encountered while fulfilling the request
     */
    public function __construct(public PushErrorCollection $errors) {}

    // Helpers ----

    /**
     * Whether any request-level errors were encountered
     */
    public function hasErrors(): bool
    {
        return $this->errors->isNotEmpty();
    }
}
