<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\Support\Sequence;
use Dru1x\ExpoPush\Support\SequenceMethods;

/**
 * A collection of PushTicket objects
 *
 * @implements Sequence<PushTicket>
 */
final class PushTicketList implements Sequence
{
    /** @use SequenceMethods<PushTicket> */
    use SequenceMethods;

    // Internals ----

    protected function valueIsValid(mixed $value): bool
    {
        return $value instanceof PushTicket;
    }
}
