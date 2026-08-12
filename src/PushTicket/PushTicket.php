<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use Dru1x\ExpoPush\Support\PushStatus;
use JsonSerializable;

abstract readonly class PushTicket implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public PushToken  $token,
        public PushStatus $status,
    ) {}

    // Helpers ----

    public function isSuccessful(): bool
    {
        return $this->status === PushStatus::Ok;
    }

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
