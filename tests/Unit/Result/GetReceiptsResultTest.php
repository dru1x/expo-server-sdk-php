<?php

namespace Dru1x\ExpoPush\Tests\Unit\Result;

use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushReceipt\FailedPushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptDetails;
use Dru1x\ExpoPush\PushReceipt\PushReceiptErrorCode;
use Dru1x\ExpoPush\PushReceipt\SuccessfulPushReceipt;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\Result\GetReceiptsResult;
use Dru1x\ExpoPush\Support\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class GetReceiptsResultTest extends TestCase
{
    #[Test]
    public function has_errors_returns_true_when_errors_are_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(),
            errors: new PushErrorCollection(
                new PushError(
                    code: PushErrorCode::Failed,
                    message: 'Failed to get push receipts',
                ),
            ),
        );

        $this->assertTrue($result->hasErrors());
    }

    #[Test]
    public function has_errors_returns_false_when_errors_are_not_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasErrors());
    }

    #[Test]
    public function has_receipts_returns_true_when_receipts_are_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(
                new SuccessfulPushReceipt('receipt-1'),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertTrue($result->hasReceipts());
    }

    #[Test]
    public function has_receipts_returns_true_when_receipts_are_not_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasReceipts());
    }

    #[Test]
    public function has_successful_receipts_returns_true_when_successful_receipts_are_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(
                new SuccessfulPushReceipt('receipt-1'),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertTrue($result->hasSuccessfulReceipts());
    }

    #[Test]
    public function has_successful_receipts_returns_true_when_successful_receipts_are_not_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(
                new FailedPushReceipt('receipt-1', 'Unknown error', new PushReceiptDetails(
                    error: PushReceiptErrorCode::Unknown,
                    expoPushToken: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                )),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasSuccessfulReceipts());
    }

    #[Test]
    public function has_failed_receipts_returns_true_when_failed_receipts_are_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(
                new FailedPushReceipt('receipt-1', 'Unknown error', new PushReceiptDetails(
                    error: PushReceiptErrorCode::Unknown,
                    expoPushToken: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                )),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertTrue($result->hasFailedReceipts());
    }

    #[Test]
    public function has_failed_receipts_returns_true_when_failed_receipts_are_not_present(): void
    {
        $result = new GetReceiptsResult(
            receipts: new PushReceiptCollection(
                new SuccessfulPushReceipt('receipt-1'),
            ),
            errors: new PushErrorCollection(),
        );

        $this->assertFalse($result->hasFailedReceipts());
    }
}
