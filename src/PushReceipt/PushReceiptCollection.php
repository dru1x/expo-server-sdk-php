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
    protected array $idMap;

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
        if (isset($this->idMap)) {
            return $this->idMap[$receiptId] ?? null;
        }

        foreach (array_reverse($this->items) as $receipt) {
            $this->idMap[$receipt->id] = $receipt;
        }

        return $this->idMap[$receiptId] ?? null;
    }

    /**
     * Add an item to this collection
     *
     * @param PushReceipt $item
     *
     * @return $this
     */
    public function add(mixed $item): static
    {
        $this->items[] = $item;
        unset($this->idMap);

        return $this;
    }

    /**
     * Add an item to this collection at a specific key
     *
     * @param int $key
     * @param PushReceipt $item
     *
     * @return $this
     */
    public function set(int|string $key, mixed $item): static
    {
        $this->items[$key] = $item;
        unset($this->idMap);

        return $this;
    }
}
