<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\ConvertsToJson;
use Dru1x\ExpoPush\Support\PushStatus;
use JsonSerializable;

abstract readonly class PushReceipt implements JsonSerializable
{
    use ConvertsToJson;

    /**
     * @param string     $id The receipt ID this receipt was returned for
     * @param PushStatus $status Whether the notification was delivered successfully
     */
    public function __construct(
        public string     $id,
        public PushStatus $status,
    ) {}

    // Helpers ----

    /**
     * Whether this receipt was returned with a status of "ok"
     */
    public function isSuccessful(): bool
    {
        return $this->status === PushStatus::Ok;
    }

    /**
     * Whether this receipt was returned with a status of "error"
     */
    public function isFailed(): bool
    {
        return $this->status === PushStatus::Error;
    }

    // Internals ----

    /**
     * @inheritDoc
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(
            get_object_vars($this),
            fn(mixed $value): bool => !is_null($value),
        );
    }
}
