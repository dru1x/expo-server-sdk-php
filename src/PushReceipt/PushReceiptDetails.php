<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use JsonSerializable;

final readonly class PushReceiptDetails implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public PushReceiptErrorCode $error,
        public ?PushToken $expoPushToken,
    ) {}
}
