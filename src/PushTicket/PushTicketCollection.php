<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\Support\Collection;
use Dru1x\ExpoPush\Support\CollectionMethods;

/**
 * A collection of PushTicket objects
 *
 * @implements Collection<int, PushTicket>
 */
final class PushTicketCollection implements Collection
{
    /** @use CollectionMethods<int, PushTicket> */
    use CollectionMethods;

    public function __construct(PushTicket ...$pushTickets)
    {
        $this->items = $pushTickets;
    }
}
