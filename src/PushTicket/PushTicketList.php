<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\Support\Sequence;
use Dru1x\ExpoPush\Support\SequenceMethods;

/**
 * A collection of PushTicket objects
 *
 * @implements Sequence<int, PushTicket>
 */
final class PushTicketList implements Sequence
{
    /** @use SequenceMethods<int, PushTicket> */
    use SequenceMethods;

    // Internals ----

    protected function valueIsValid(mixed $value): bool
    {
        return $value instanceof PushTicket;
    }
}
