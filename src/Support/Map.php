<?php

namespace Dru1x\ExpoPush\Support;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @template-extends Collection<TKey, TValue>
 */
interface Map extends Collection
{
    // Mutators ----

    /**
     * Add a value to this map at the given key.
     *
     * @param TKey $key
     * @param TValue $value
     */
    public function put(int|string $key, mixed $value): void;

    /**
     * Add multiple values to this map at once.
     *
     * @param Map<TKey, TValue>|array<TKey, TValue> $values
     */
    public function putAll(self|array $values): void;

    /**
     * Merge two maps into one and return the result.
     *
     * Keys are preserved, with values from the other map replacing any that share a key (as with `array_replace`).
     *
     * @param Map<TKey, TValue> $other
     *
     * @return static
     */
    public function merge(self $other): static;
}
