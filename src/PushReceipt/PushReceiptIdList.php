<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Sequence;
use Dru1x\ExpoPush\Support\SequenceMethods;

/**
 * A collection of push receipt IDs
 *
 * @implements Sequence<string>
 */
final class PushReceiptIdList implements Sequence
{
    /** @use SequenceMethods<string> */
    use SequenceMethods;

    // Internals ----

    protected function valueIsValid(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}
