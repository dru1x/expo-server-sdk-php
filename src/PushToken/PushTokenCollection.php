<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\Collection;
use Dru1x\ExpoPush\Support\CollectionMethods;
use InvalidArgumentException;

/**
 * A collection of PushToken objects
 */
final class PushTokenCollection implements Collection
{
    /** @use CollectionMethods<int, PushToken> */
    use CollectionMethods;

    public function __construct(PushToken ...$pushTokens)
    {
        $this->items = $pushTokens;
    }

    public static function fromArray(array $data): self
    {
        $tokens = array_map(PushToken::fromString(...), $data);

        return new self(...$tokens);
    }

    /**
     * Remove and return the first PushToken(s) from the collection
     */
    public function shift(int $count = 1): self
    {
        if ($count < 0) {
            throw new InvalidArgumentException('Number of shifted items must be greater than 0');
        }

        if ($count === 0 || $this->isEmpty()) {
            return new self();
        }

        return new self(
            ...array_splice($this->items, 0, min($count, $this->count()))
        );
    }
}
