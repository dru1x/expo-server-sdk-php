<?php

namespace Dru1x\ExpoPush\PushTicket;

use Dru1x\ExpoPush\Support\Map;
use Dru1x\ExpoPush\Support\MapMethods;

/**
 * A collection of PushTicket objects, keyed by a notification index
 *
 * @phpstan-type NotificationIndex non-negative-int
 *
 * @implements Map<NotificationIndex, PushTicket>
 */
final class PushTicketMap implements Map
{
    /** @use MapMethods<NotificationIndex, PushTicket> */
    use MapMethods;

    // Validation ----

    protected function keyIsValid(mixed $key): bool
    {
        return is_int($key) && $key >= 0;
    }

    protected function valueIsValid(mixed $value): bool
    {
        return $value instanceof PushTicket;
    }
}
