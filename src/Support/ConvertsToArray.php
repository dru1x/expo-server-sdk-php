<?php

namespace Dru1x\ExpoPush\Support;

trait ConvertsToArray
{
    /**
     * Convert this object to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
