<?php

namespace Dru1x\ExpoPush\Support;

/**
 * Standard sequence methods
 *
 * @template TKey of int
 * @template TValue
 */
trait SequenceMethods
{
    /** @use CollectionMethods<TKey, TValue> */
    use CollectionMethods;

    // Setup ----

    /**
     * @param array<array-key, TValue> $items
     */
    public function __construct(array $items = [])
    {
        $this->addAll($items);
    }

    // Mutators ----

    public function add(mixed $value): void
    {
        $this->validateValue($value);

        $this->items[] = $value;
    }

    public function addAll(Sequence|array $values): void
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function merge(Sequence $other): static
    {
        return new static(
            array_values(
                array_merge($this->items, $other->toArray()),
            ),
        );
    }

    public function filter(callable $callback): static
    {
        return new static(
            array_values(
                array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH),
            ),
        );
    }

    // Accessors ----

    public function first(): mixed
    {
        $key = array_key_first($this->items);

        return $key === null ? null : $this->items[$key];
    }

    public function last(): mixed
    {
        $key = array_key_last($this->items);

        return $key === null ? null : $this->items[$key];
    }
}
