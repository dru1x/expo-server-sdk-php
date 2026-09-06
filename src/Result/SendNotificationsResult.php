<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\Support\Result;

final readonly class SendNotificationsResult extends Result
{
    /**
     * @param PushTicketCollection $tickets The resulting tickets, ordered to match the sent push messages
     * @param PushErrorCollection  $errors Any request-level errors encountered while sending
     */
    public function __construct(public PushTicketCollection $tickets, PushErrorCollection $errors)
    {
        parent::__construct($errors);
    }

    /**
     * Whether any tickets were returned
     */
    public function hasTickets(): bool
    {
        return $this->tickets->count() > 0;
    }

    /**
     * Whether at least one ticket was successful
     */
    public function hasSuccessfulTickets(): bool
    {
        foreach ($this->tickets as $ticket) {
            if ($ticket->isSuccessful()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether at least one ticket failed
     */
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
