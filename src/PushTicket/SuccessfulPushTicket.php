<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;

final readonly class SuccessfulPushTicket extends PushTicket
{
    /**
     * @param PushToken $token The token this ticket was returned for
     * @param string    $receiptId The ID to use when later fetching this notification's delivery receipt
     */
    public function __construct(PushToken $token, public string $receiptId)
    {
        parent::__construct($token, PushStatus::Ok);
    }
}
