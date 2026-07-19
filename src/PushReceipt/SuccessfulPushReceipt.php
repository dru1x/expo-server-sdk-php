<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\PushStatus;

final readonly class SuccessfulPushReceipt extends PushReceipt
{
    public function __construct(string $id)
    {
        parent::__construct($id, PushStatus::Ok);
    }
}
