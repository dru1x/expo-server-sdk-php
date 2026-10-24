<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use JsonSerializable;

final readonly class PushTicketDetails implements JsonSerializable
{
    use ConvertsToJson;

    public function __construct(
        public PushTicketErrorCode $error,
        public ?PushToken          $expoPushToken,
    ) {}
}
