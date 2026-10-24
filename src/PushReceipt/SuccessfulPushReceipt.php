<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\PushStatus;

final readonly class SuccessfulPushReceipt extends PushReceipt
{
    /**
     * @param string $id The receipt ID this receipt was returned for
     */
    public function __construct(string $id)
    {
        parent::__construct($id, PushStatus::Ok);
    }
}
