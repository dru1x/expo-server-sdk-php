<?php

namespace Dru1x\ExpoPush\Support;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @template-extends IteratorAggregate<TKey, TValue>
 */
interface Collection extends Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @param TValue ...$items
     */
    public static function make(mixed ...$items): static;

    /**
     * @param iterable<TKey, TValue> ...$items
     */
    public static function fromIterable(iterable $items): static;

    // Altering ----

    /**
     * Add an item to this collection
     *
     * @param TValue $item
     *
     * @return $this
     */
    public function add(mixed $item): static;

    /**
     * Add an item to this collection at a specific key
     *
     * @param TKey $key
     * @param TValue $item
     *
     * @return $this
     */
    public function set(int|string $key, mixed $item): static;

    // Retrieving ----

    /**
     * Get an item from the collection by its key, or null if not present
     *
     * @param TKey $key
     *
     * @return TValue|null
     */
    public function get(int|string $key): mixed;

    /**
     * Get all items as an array
     *
     * @return array<TKey, TValue>
     */
    public function all(): array;

    /**
     * Get all items as an array
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array;

    /**
     * Retrieve an iterator for the collection
     *
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable;

    // Helpers ----

    /**
     * Break the collection into chunks of the provided length
     *
     * @param int<1, max> $length
     *
     * @return iterable<static>
     */
    public function chunk(int $length): iterable;

    /**
     * Check if the collection contains a given value
     *
     * @param TValue $value
     *
     * @return bool
     */
    public function contains(mixed $value): bool;

    /**
     * Check if the collection doesnt contain a given value
     *
     * @param TValue $value
     *
     * @return bool
     */
    public function doesntContain(mixed $value): bool;

    /**
     * Get the number of items in the collection
     *
     * @return int
     */
    public function count(): int;

    /**
     * Filter the collection to items where the callback returns a truthy value
     *
     * @param ?callable(TValue, TKey): bool $callable
     */
    public function filter(?callable $callable): static;

    /**
     * Check if the collection contains no items
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Check if the collection contains any items
     *
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * Create a new iterable by running the provided callable on each of the items of this collection
     *
     * @template TMap of mixed
     *
     * @param callable(TValue): TMap $callable
     *
     * @return iterable<TKey, TMap>
     */
    public function map(callable $callable): iterable;

    /**
     * Merge a set of iterables into a single iterable
     *
     * @param iterable<TKey, TValue> ...$iterables
     */
    public function merge(iterable ...$iterables): static;

    /**
     * Reduce the collection into a single value
     *
     * @template TReturnType
     *
     * @param  callable(TReturnType, TValue): TReturnType  $callback
     * @param  TReturnType  $initial
     * @return TReturnType
     */
    public function reduce(callable $callback, mixed $initial = null): mixed;

    /**
     * Filter the collection to items where the callback returns a falsy value
     *
     * @param ?callable(TValue, TKey): bool $callable
     */
    public function reject(?callable $callable): static;

    /**
     * Create a new collection with the same items but consecutive integer keys
     */
    public function values(): static;
}