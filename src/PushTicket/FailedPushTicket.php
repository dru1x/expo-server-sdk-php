<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;

final readonly class FailedPushTicket extends PushTicket
{
    /**
     * @param PushToken         $token The token this ticket was returned for
     * @param string            $message A human-readable description of the error
     * @param PushTicketDetails $details Further structured detail about the error
     */
    public function __construct(PushToken $token, public string $message, public PushTicketDetails $details)
    {
        parent::__construct($token, PushStatus::Error);
    }
}
