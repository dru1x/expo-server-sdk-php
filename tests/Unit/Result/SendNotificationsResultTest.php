<?php

namespace Dru1x\ExpoPush\Tests\Unit\Result;

use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushTicket\FailedPushTicket;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\PushTicket\PushTicketDetails;
use Dru1x\ExpoPush\PushTicket\PushTicketErrorCode;
use Dru1x\ExpoPush\PushTicket\SuccessfulPushTicket;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Result\SendNotificationsResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SendNotificationsResultTest extends TestCase
{
    #[Test]
    public function has_errors_returns_true_when_errors_are_present(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(),
            errors: new PushErrorCollection(
                new PushError(
                    code: PushErrorCode::Failed,
                    message: 'Failed to send push notification',
                ),
            ),
        );

        $this->assertTrue($result->hasErrors());
    }

    #[Test]
    public function has_errors_returns_false_when_errors_are_not_present(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasErrors());
    }

    #[Test]
    public function has_tickets_returns_true_when_tickets_are_present(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(
                new SuccessfulPushTicket(
                    token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                    receiptId: 'receipt-1',
                ),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertTrue($result->hasTickets());
    }

    #[Test]
    public function has_tickets_returns_false_when_tickets_are_not_present(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasTickets());
    }

    #[Test]
    public function has_successful_tickets_returns_true_when_successful_tickets_are_present(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(
                new SuccessfulPushTicket(
                    token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                    receiptId: 'receipt-1',
                ),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertTrue($result->hasSuccessfulTickets());
    }

    #[Test]
    public function has_successful_tickets_returns_false_when_successful_tickets_are_not_present(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(
                new FailedPushTicket(
                    token: $token,
                    message: 'Unknown error',
                    details: new PushTicketDetails(
                        error: PushTicketErrorCode::Unknown,
                        expoPushToken: $token,
                    ),
                ),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasSuccessfulTickets());
    }

    #[Test]
    public function has_failed_tickets_returns_true_when_failed_tickets_are_present(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(
                new FailedPushTicket(
                    token: $token,
                    message: 'Unknown error',
                    details: new PushTicketDetails(
                        error: PushTicketErrorCode::Unknown,
                        expoPushToken: $token,
                    ),
                ),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertTrue($result->hasFailedTickets());
    }

    #[Test]
    public function has_failed_tickets_returns_false_when_failed_tickets_are_not_present(): void
    {
        $result = new SendNotificationsResult(
            tickets: new PushTicketCollection(
                new SuccessfulPushTicket(
                    token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                    receiptId: 'receipt-1',
                ),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasFailedTickets());
    }
}
