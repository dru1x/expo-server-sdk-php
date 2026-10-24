<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Sequence;
use Dru1x\ExpoPush\Support\SequenceMethods;

/**
 * A collection of push receipt IDs
 *
 * @implements Sequence<int, string>
 */
final class PushReceiptIdList implements Sequence
{
    /** @use SequenceMethods<int, string> */
    use SequenceMethods;

    // Internals ----

    protected function valueIsValid(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
