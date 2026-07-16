<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushReceipt;

use Dru1x\ExpoPush\PushReceipt\FailedPushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptDetails;
use Dru1x\ExpoPush\PushReceipt\PushReceiptErrorCode;
use Dru1x\ExpoPush\PushToken\PushToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FailedPushReceiptTest extends TestCase
{
    #[Test]
    public function json_encode_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $ticket = new FailedPushReceipt(
            id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            message: '"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]" is not a registered push notification recipient',
            details: new PushReceiptDetails(
                error: PushReceiptErrorCode::DeviceNotRegistered,
                expoPushToken: $token,
            ),
        );

        $expectedJson = <<<JSON
            {
                "details": {
                    "error": "DeviceNotRegistered",
                    "expoPushToken": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
                },
                "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "message": "\"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]\" is not a registered push notification recipient",
                "status": "error"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($ticket));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $ticket = new FailedPushReceipt(
            id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            message: '"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]" is not a registered push notification recipient',
            details: new PushReceiptDetails(
                error: PushReceiptErrorCode::DeviceNotRegistered,
                expoPushToken: $token,
            ),
        );

        $expectedJson = <<<JSON
            {
                "details": {
                    "error": "DeviceNotRegistered",
                    "expoPushToken": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
                },
                "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "message": "\"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]\" is not a registered push notification recipient",
                "status": "error"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $ticket->toJson());
    }
}
