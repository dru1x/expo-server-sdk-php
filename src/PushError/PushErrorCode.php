<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\PushError;

enum PushErrorCode: string
{
    case Unknown = 'UNKNOWN';
    case Failed = 'FAILED';
    case Unauthorized = 'UNAUTHORIZED';
    case TooManyRequests = 'TOO_MANY_REQUESTS';
    case PushTooManyExperienceIds = 'PUSH_TOO_MANY_EXPERIENCE_IDS';
    case PushTooManyNotifications = 'PUSH_TOO_MANY_NOTIFICATIONS';
    case PushTooManyReceipts = 'PUSH_TOO_MANY_RECEIPTS';
}
