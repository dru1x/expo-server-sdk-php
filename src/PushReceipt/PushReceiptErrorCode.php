<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushReceipt;

enum PushReceiptErrorCode: string
{
    case Unknown = 'Unknown';
    case DeviceNotRegistered = 'DeviceNotRegistered';
    case InvalidCredentials = 'InvalidCredentials';
    case MessageRateExceeded = 'MessageRateExceeded';
    case MessageTooBig = 'MessageTooBig';
    case MismatchSenderId = 'MismatchSenderId';
}
