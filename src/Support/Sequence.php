<?php

namespace Dru1x\ExpoPush\Support;

/**
 * @template TKey of int
 * @template TValue
 *
 * @template-extends Collection<TKey, TValue>
 */
interface Sequence extends Collection
{
    // Mutators ----

    /**
     * Add a value to the end of this sequence.
     *
     * @param TValue $value
     */
    public function add(mixed $value): void;

    /**
     * Add multiple values to the end of this sequence.
     *
     * @param Sequence<TKey, TValue>|array<TKey, TValue> $values
     */
    public function addAll(self|array $values): void;

    /**
     * Merge two sequences into one and return the result.
     *
     * This follows the same merging rules as `array_merge`.
     *
     * @param Sequence<TKey, TValue> $other
     *
     * @return static
     */
    public function merge(self $other): static;

    // Accessors ----

    /**
     * Get the first value in this sequence.
     *
     * @return TValue|null
     */
    public function first(): mixed;

    /**
     * Get the last value in this sequence.
     *
     * @return TValue|null
     */
    public function last(): mixed;
}
