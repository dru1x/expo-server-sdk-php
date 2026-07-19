<?php

namespace Dru1x\ExpoPush\Support;

use JsonException;

trait ConvertsFromJson
{
    use ConvertsFromArray;

    /**
     * Create an object from a JSON string
     *
     * @throws JsonException
     */
    public static function fromJson(string $json): static
    {
        return static::fromArray(
            static::jsonDecode($json),
        );
    }

    /**
     * @throws JsonException
     */
    public static function jsonDecode(string $json): mixed
    {
        return json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
    }
}
