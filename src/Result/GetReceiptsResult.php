<?php

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorList;
use Dru1x\ExpoPush\PushReceipt\PushReceiptMap;
use Dru1x\ExpoPush\Support\Result;

final readonly class GetReceiptsResult extends Result
{
    public function __construct(public PushReceiptMap $receipts, PushErrorList $errors)
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
