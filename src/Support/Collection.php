<?php

namespace Dru1x\ExpoPush\Support;

use Countable;
use IteratorAggregate;
use JsonSerializable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @template-extends IteratorAggregate<TKey, TValue>
 */
interface Collection extends Countable, IteratorAggregate, JsonSerializable
{
    // Setup ----

    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(array $items = []);

    /**
     * @param iterable<TKey, TValue> $items
     */
    public static function make(iterable $items = []): static;

    // Mutators ----

    /**
     * Filter the collection to items for which the callback returns a truthy value.
     *
     * @param callable(TValue, TKey): bool $callback
     */
    public function filter(callable $callback): static;

    /**
     * Create a new iterable by running the provided callable on each of the items of this collection.
     *
     * @template TMap of mixed
     *
     * @param callable(TValue, TKey): TMap $callback
     *
     * @return iterable<TKey, TMap>
     */
    public function map(callable $callback): iterable;

    /**
     * Reduce the collection into a single value
     *
     * @template TReturn
     *
     * @param callable(TReturn, TValue): TReturn $callback
     * @param TReturn $initial
     *
     * @return TReturn
     */
    public function reduce(callable $callback, mixed $initial = null): mixed;

    /**
     * Break the collection into chunks of the provided length
     *
     * @param int<1, max> $length
     *
     * @return iterable<static>
     */
    public function chunk(int $length): iterable;

    // Accessors ----

    /**
     * Get a value at the given key.
     *
     * Returns the value, or `null` if they key does not exist.
     *
     * @param TKey $key
     *
     * @return TValue|null
     */
    public function get(mixed $key): mixed;

    /**
     * Check if this collection is empty.
     */
    public function isEmpty(): bool;

    /**
     * Check if this collection is not empty.
     */
    public function isNotEmpty(): bool;

    /**
     * Check if the given value is present in the collection.
     *
     * @param TValue $value
     */
    public function contains(mixed $value): bool;

    /**
     * Check if the given value is not present in the collection.
     *
     * @param TValue $value
     */
    public function doesNotContain(mixed $value): bool;

    /**
     * Get an array representation of this collection.
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array;
}
