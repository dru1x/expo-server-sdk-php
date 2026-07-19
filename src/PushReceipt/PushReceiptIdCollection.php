<?php

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Collection;
use Dru1x\ExpoPush\Support\CollectionMethods;

/**
 * A collection of push receipt IDs
 *
 * @implements Collection<int, string>
 */
final class PushReceiptIdCollection implements Collection
{
    /** @use CollectionMethods<int, string> */
    use CollectionMethods;

    public function __construct(string ...$pushReceiptIds)
    {
        $this->items = $pushReceiptIds;
    }
}
