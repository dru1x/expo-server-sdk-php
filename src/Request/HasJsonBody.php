<?php

namespace Dru1x\ExpoPush\Request;

use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Traits\Body\HasJsonBody as SaloonHasBody;

trait HasJsonBody
{
    use SaloonHasBody {
        body as protected buildBody;
    }

    public function body(): JsonBodyRepository
    {
        return $this->buildBody()->setJsonFlags(
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}