<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushError;

use Dru1x\ExpoPush\Support\ConvertsToJson;
use JsonSerializable;

final readonly class PushError implements JsonSerializable
{
    use ConvertsToJson;

    /**
     * @param ?array<string, mixed> $details
     */
    public function __construct(
        public PushErrorCode $code,
        public string        $message,
        public ?array        $details = null,
        public ?int          $startIndex = null,
        public ?int          $endIndex = null,
    ) {}
}
