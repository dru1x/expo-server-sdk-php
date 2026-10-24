<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Map;
use Dru1x\ExpoPush\Support\MapMethods;

/**
 * A collection of PushReceipt objects, keyed by their ID
 *
 * @phpstan-type PushReceiptId non-empty-string
 *
 * @implements Map<PushReceiptId, PushReceipt>
 */
final class PushReceiptMap implements Map
{
    /** @use MapMethods<PushReceiptId, PushReceipt> */
    use MapMethods;

    // Validation ----

    protected function keyIsValid(mixed $key): bool
    {
        return is_string($key) && trim($key) !== '';
    }

    protected function valueIsValid(mixed $value): bool
    {
        return $value instanceof PushReceipt;
    }
}
