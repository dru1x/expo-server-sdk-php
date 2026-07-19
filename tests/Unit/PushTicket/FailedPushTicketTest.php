<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushTicket;

use Dru1x\ExpoPush\PushTicket\FailedPushTicket;
use Dru1x\ExpoPush\PushTicket\PushTicketDetails;
use Dru1x\ExpoPush\PushTicket\PushTicketErrorCode;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FailedPushTicketTest extends TestCase
{
    #[Test]
    public function properties_are_accessible(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $message = '"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]" is not a registered push notification recipient';

        $details = new PushTicketDetails(
            error: PushTicketErrorCode::DeviceNotRegistered,
            expoPushToken: $token,
        );

        $ticket = new FailedPushTicket($token, $message, $details);

        $this->assertEquals($token, $ticket->token);
        $this->assertEquals($message, $ticket->message);
        $this->assertEquals($details, $ticket->details);
        $this->assertEquals(PushStatus::Error, $ticket->status);
    }

    #[Test]
    public function json_encode_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $ticket = new FailedPushTicket(
            token: $token,
            message: '"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]" is not a registered push notification recipient',
            details: new PushTicketDetails(
                error: PushTicketErrorCode::DeviceNotRegistered,
                expoPushToken: $token,
            ),
        );

        $expectedJson = <<<JSON
            {
              "details": {
                "error": "DeviceNotRegistered",
                "expoPushToken": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
              },
              "message": "\"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]\" is not a registered push notification recipient",
              "status": "error",
              "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($ticket));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $ticket = new FailedPushTicket(
            token: $token,
            message: '"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]" is not a registered push notification recipient',
            details: new PushTicketDetails(
                error: PushTicketErrorCode::DeviceNotRegistered,
                expoPushToken: $token,
            ),
        );

        $expectedJson = <<<JSON
            {
              "details": {
                "error": "DeviceNotRegistered",
                "expoPushToken": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
              },
              "message": "\"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]\" is not a registered push notification recipient",
              "status": "error",
              "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $ticket->toJson());
    }
}
