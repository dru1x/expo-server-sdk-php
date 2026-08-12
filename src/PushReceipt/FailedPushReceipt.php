<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;

final readonly class FailedPushReceipt extends PushReceipt
{
    /**
     * @param string              $id The receipt ID this receipt was returned for
     * @param string              $message A human-readable description of the error
     * @param PushReceiptDetails  $details Further structured detail about the error
     */
    public function __construct(string $id, public string $message, public PushReceiptDetails $details)
    {
        parent::__construct($id, PushStatus::Error);
    }
}
