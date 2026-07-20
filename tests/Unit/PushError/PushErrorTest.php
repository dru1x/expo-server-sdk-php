<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushError;

use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PushErrorTest extends TestCase
{
    #[Test]
    public function json_encode_returns_value(): void
    {
        $error = new PushError(
            code: PushErrorCode::PushTooManyNotifications,
            message: 'Too many notifications',
            details: ['field' => 'to'],
            startIndex: 0,
            endIndex: 5,
        );

        $expectedJson = <<<JSON
            {
                "code": "PUSH_TOO_MANY_NOTIFICATIONS",
                "message": "Too many notifications",
                "details": {"field": "to"},
                "startIndex": 0,
                "endIndex": 5
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($error));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $error = new PushError(
            code: PushErrorCode::PushTooManyNotifications,
            message: 'Too many notifications',
            details: ['field' => 'to'],
            startIndex: 0,
            endIndex: 5,
        );

        $expectedJson = <<<JSON
            {
                "code": "PUSH_TOO_MANY_NOTIFICATIONS",
                "message": "Too many notifications",
                "details": {"field": "to"},
                "startIndex": 0,
                "endIndex": 5
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $error->toJson());
    }
}
