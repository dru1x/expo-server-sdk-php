<?php

namespace Dru1x\ExpoPush\PushError;

use Dru1x\ExpoPush\Support\Collection;
use Dru1x\ExpoPush\Support\CollectionMethods;

/**
 * A collection of PushError objects
 *
 * @implements Collection<int, PushError>
 */
final class PushErrorCollection implements Collection
{
    /** @use CollectionMethods<int, PushError> */
    use CollectionMethods;

    public function __construct(PushError ...$errors)
    {
        $this->items = $errors;
    }
}