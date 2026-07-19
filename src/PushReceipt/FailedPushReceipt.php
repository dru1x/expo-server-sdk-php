<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;

final readonly class FailedPushReceipt extends PushReceipt
{
    public function __construct(string $id, public string $message, public PushReceiptDetails $details)
    {
        parent::__construct($id, PushStatus::Error);
    }
}
