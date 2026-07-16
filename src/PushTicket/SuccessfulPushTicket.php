<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;

final readonly class SuccessfulPushTicket extends PushTicket
{
    public function __construct(PushToken $token, public string $receiptId)
    {
        parent::__construct($token, PushStatus::Ok);
    }
}
