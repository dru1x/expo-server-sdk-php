<?php

namespace Dru1x\ExpoPush\Support;

use InvalidArgumentException;

/**
 * Standard map methods
 *
 * @template TKey of array-key
 * @template TValue
 */
trait MapMethods
{
    /** @use CollectionMethods<TKey, TValue> */
    use CollectionMethods;

    // Setup ----

    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(array $items = [])
    {
        $this->putAll($items);
    }

    // Mutators ----

    public function put(int|string $key, mixed $value): void
    {
        $this->validateKey($key);
        $this->validateValue($value);

        $this->items[$key] = $value;
    }

    public function putAll(Map|array $values): void
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value);
        }
    }

    public function merge(Map $other): static
    {
        return new static(
            array_replace($this->items, $other->toArray()),
        );
    }

    public function filter(callable $callback): static
    {
        return new static(
            array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH),
        );
    }

    // Internals ----

    /**
     * Validate a key to ensure it is acceptable.
     *
     * @param mixed $key
     *
     * @return void
     */
    protected function validateKey(mixed $key): void
    {
        if (!$this->keyIsValid($key)) {
            throw new InvalidArgumentException('Invalid key of type ' . get_debug_type($key));
        }
    }

    /**
     * Check if a key is valid for this map.
     *
     * @param mixed $key
     *
     * @return bool
     */
    abstract protected function keyIsValid(mixed $key): bool;
}
