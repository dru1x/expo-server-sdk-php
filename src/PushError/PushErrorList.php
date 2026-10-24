<?php

namespace Dru1x\ExpoPush\PushError;

use Dru1x\ExpoPush\Support\Sequence;
use Dru1x\ExpoPush\Support\SequenceMethods;

/**
 * A collection of PushError objects
 *
 * @implements Sequence<PushError>
 */
final class PushErrorList implements Sequence
{
    /** @use SequenceMethods<PushError> */
    use SequenceMethods;

    // Internals ----

    protected function valueIsValid(mixed $value): bool
    {
        return $value instanceof PushError;
    }
}
