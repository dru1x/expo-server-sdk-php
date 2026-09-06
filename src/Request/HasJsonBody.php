<?php

namespace Dru1x\ExpoPush\Request;

use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Traits\Body\HasJsonBody as SaloonHasBody;

trait HasJsonBody
{
    use SaloonHasBody {
        body as protected getBody;
    }

    public function body(): JsonBodyRepository
    {
        return $this->getBody()->setJsonFlags(
            JSON_THROW_ON_ERROR | $this->jsonFormattingFlags(),
        );
    }

    // Internals ----

    protected function jsonFormattingFlags(): int
    {
        return JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    }
}
