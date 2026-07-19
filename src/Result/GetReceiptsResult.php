<?php

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\Support\Result;

final readonly class GetReceiptsResult extends Result
{
    public function __construct(public PushReceiptCollection $receipts, PushErrorCollection $errors)
    {
        parent::__construct($errors);
    }

    public function hasReceipts(): bool
    {
        return $this->receipts->count() > 0;
    }

    public function hasSuccessfulReceipts(): bool
    {
        foreach ($this->receipts as $receipt) {
            if ($receipt->isSuccessful()) {
                return true;
            }
        }

        return false;
    }

    public function hasFailedReceipts(): bool
    {
        foreach ($this->receipts as $receipt) {
            if ($receipt->isFailed()) {
                return true;
            }
        }

        return false;
    }
}
