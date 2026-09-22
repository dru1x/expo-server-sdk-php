<?php

namespace Dru1x\ExpoPush\Support;

use JsonException;

trait ConvertsToJson
{
    use ConvertsToArray;

    /**
     * @inheritDoc
     *
     * @return array<array-key, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert this object to a JSON string
     *
     * @return string
     * @throws JsonException
     */
    public function toJson(): string
    {
        return json_encode($this, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
