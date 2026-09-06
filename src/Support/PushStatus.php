<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Support;

enum PushStatus: string
{
    case Ok = 'ok';
    case Error = 'error';
}
