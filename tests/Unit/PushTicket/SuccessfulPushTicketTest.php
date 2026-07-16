<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushTicket;

use Dru1x\ExpoPush\PushTicket\SuccessfulPushTicket;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Support\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SuccessfulPushTicketTest extends TestCase
{
    #[Test]
    public function properties_are_accessible(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $receiptId = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';

        $ticket = new SuccessfulPushTicket($token, $receiptId);

        $this->assertEquals($token, $ticket->token);
        $this->assertEquals($receiptId, $ticket->receiptId);
        $this->assertEquals(PushStatus::Ok, $ticket->status);
    }

    #[Test]
    public function json_encode_returns_value(): void
    {
        $ticket = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );

        $expectedJson = <<<JSON
            {
              "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
              "status": "ok", 
              "receiptId": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($ticket));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $ticket = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );

        $expectedJson = <<<JSON
            {
              "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
              "status": "ok", 
              "receiptId": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $ticket->toJson());
    }
}
