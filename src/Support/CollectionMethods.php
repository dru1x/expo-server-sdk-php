<?php

namespace Dru1x\ExpoPush\Support;

use ArrayIterator;
use InvalidArgumentException;
use Traversable;

/**
 * Standard collection methods
 *
 * @template TKey of array-key
 * @template TValue
 */
trait CollectionMethods
{
    use ConvertsFromJson;
    use ConvertsToJson;

    /**
     * The values contained in the collection
     *
     * @var array<TKey, TValue>
     */
    protected array $values = [];

    // Setup ----

    public static function make(iterable $items = []): static
    {
        return new static(iterator_to_array($items));
    }

    /**
     * @param array<TKey, TValue> $data
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    // Mutators ----

    public function map(callable $callback): iterable
    {
        $keys = array_keys($this->values);
        $values = array_map($callback, $this->values, $keys);

        return array_combine($keys, $values);
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->values, $callback, $initial);
    }

    public function chunk(int $length): iterable
    {
        return array_map(static::make(...), array_chunk($this->values, $length, preserve_keys: true));
    }

    // Accessors ----

    public function get(mixed $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function contains(mixed $value): bool
    {
        return in_array($value, $this->values, true);
    }

    public function doesNotContain(mixed $value): bool
    {
        return !$this->contains($value);
    }

    public function toArray(): array
    {
        return $this->values;
    }

    // Countable ----

    public function count(): int
    {
        return count($this->values);
    }

    // IteratorAggregate ----

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    // Internals ----

    /**
     * Validate a value to ensure it is acceptable.
     *
     * @param mixed $value
     *
     * @return void
     */
    protected function validateValue(mixed $value): void
    {
        if (!$this->valueIsValid($value)) {
            throw new InvalidArgumentException('Invalid value of type ' . get_debug_type($value));
        }
    }

    /**
     * Check if a value is valid for this collection.
     *
     * @param mixed $value
     *
     * @return bool
     */
    abstract protected function valueIsValid(mixed $value): bool;
}
