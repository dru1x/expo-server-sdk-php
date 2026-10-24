<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\Sequence;
use Dru1x\ExpoPush\Support\SequenceMethods;
use InvalidArgumentException;

/**
 * A collection of PushToken objects
 *
 * @implements Sequence<PushToken>
 */
final class PushTokenList implements Sequence
{
    /** @use SequenceMethods<PushToken> */
    use SequenceMethods;

    /**
     * @param array<string> $data
     */
    public static function fromArray(array $data): self
    {
        $tokens = array_map(PushToken::fromString(...), $data);

        return new self($tokens);
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
            array_splice($this->values, 0, $count),
        );
    }

    // Internals ----
    protected function valueIsValid(mixed $value): bool
    {
        return $value instanceof PushToken;
    }
}
