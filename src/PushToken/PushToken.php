<?php

namespace Dru1x\ExpoPush\PushToken;

use Dru1x\ExpoPush\Support\ConvertsFromJson;
use Dru1x\ExpoPush\Support\ConvertsToJson;
use InvalidArgumentException;
use JsonException;
use JsonSerializable;
use Stringable;

final readonly class PushToken implements JsonSerializable, Stringable
{
    use ConvertsFromJson, ConvertsToJson;

    public function __construct(public string $value)
    {
        if ($this->isNotValidTokenValue($value)) {
            throw new InvalidArgumentException("'$value' is not a valid push token");
        }
    }

    // Helpers ----

    public function toString(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * @throws JsonException
     */
    public static function fromJson(string $json): self
    {
        return self::fromString(
            self::jsonDecode($json)
        );
    }

    // Internals ----

    public function __toString(): string
    {
        return $this->toString();
    }

    /** @inheritDoc */
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    /**
     * Validates a string is of a suitable format to be used as a Push Token
     *
     * This is undocumented behaviour, so just mirrors the official Expo Node SDK
     *
     * @see https://github.com/expo/expo-server-sdk-node
     */
    protected function isValidTokenValue(string $value): bool
    {
        if(str_starts_with($value, 'ExponentPushToken[') and str_ends_with($value, ']')) {
            return true;
        }

        if(str_starts_with($value, 'ExpoPushToken[') and str_ends_with($value, ']')) {
            return true;
        }

        // UUID-like legacy format
        if(preg_match('/^[a-z\d]{8}-[a-z\d]{4}-[a-z\d]{4}-[a-z\d]{4}-[a-z\d]{12}$/', $this->value)) {
            return true;
        }

        return false;
    }

    protected function isNotValidTokenValue(string $value): bool
    {
        return ! $this->isValidTokenValue($value);
    }
}
