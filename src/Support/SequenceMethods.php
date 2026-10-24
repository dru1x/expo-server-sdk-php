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
     * @param array<array-key, TValue> $values
     */
    public function __construct(array $values = [])
    {
        $this->push(...array_values($values));
    }

    // Mutators ----

    public function push(mixed ...$values): void
    {
        foreach ($values as $value) {
            $this->validateValue($value);
            $this->values[] = $value;
        }
    }

    public function merge(Sequence $other): static
    {
        return new static(
            array_values(
                array_merge($this->values, $other->toArray()),
            ),
        );
    }

    public function filter(callable $callback): static
    {
        return new static(
            array_values(
                array_filter($this->values, $callback, ARRAY_FILTER_USE_BOTH),
            ),
        );
    }

    // Accessors ----

    public function first(): mixed
    {
        $key = array_key_first($this->values);

        return $key === null ? null : $this->values[$key];
    }

    public function last(): mixed
    {
        $key = array_key_last($this->values);

        return $key === null ? null : $this->values[$key];
    }
}
