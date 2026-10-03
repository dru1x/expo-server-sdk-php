<?php

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

    /** @var array<string, PushReceipt> */
    protected array $map;

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
        if(isset($this->map)) {
            return $this->map[$receiptId] ?? null;
        }

        foreach ($this->items as $receipt) {
            $this->map[$receipt->id] = $receipt;
        }

        return $this->map[$receiptId] ?? null;
    }
}
