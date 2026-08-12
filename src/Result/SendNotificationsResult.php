<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\Support\Result;

final readonly class SendNotificationsResult extends Result
{
    public function __construct(public PushTicketCollection $tickets, PushErrorCollection $errors)
    {
        parent::__construct($errors);
    }

    public function hasTickets(): bool
    {
        return $this->tickets->count() > 0;
    }

    public function hasSuccessfulTickets(): bool
    {
        foreach ($this->tickets as $ticket) {
            if ($ticket->isSuccessful()) {
                return true;
            }
        }

        return false;
    }

    public function hasFailedTickets(): bool
    {
        foreach ($this->tickets as $ticket) {
            if ($ticket->isFailed()) {
                return true;
            }
        }

        return false;
    }
}
