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
     * Add one or more values to the end of this sequence.
     *
     * @param TValue ...$values
     *
     * @return void
     */
    public function push(mixed ...$values): void;

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
