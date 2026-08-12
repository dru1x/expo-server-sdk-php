<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushReceipt;

use Dru1x\ExpoPush\Support\Collection;
use Dru1x\ExpoPush\Support\CollectionMethods;

/**
 * A collection of PushReceipt objects
 *
 * @implements Collection<int, PushReceipt>
 */
final class PushReceiptCollection implements Collection
{
    /** @use CollectionMethods<int, PushReceipt> */
    use CollectionMethods;

    public function __construct(PushReceipt ...$pushReceipts)
    {
        $this->items = $pushReceipts;
    }

    // Helpers ----

    /**
     * Find a push receipt by its ID
     *
     * @param string $receiptId
     *
     * @return PushReceipt|null
     */
    public function getById(string $receiptId): ?PushReceipt
    {
        foreach ($this->items as $receipt) {
            if ($receipt->id === $receiptId) {
                return $receipt;
            }
        }

        return null;
    }
}
