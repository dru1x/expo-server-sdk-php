<?php

declare(strict_types=1);

namespace Dru1x\ExpoPush\Result;

use Dru1x\ExpoPush\PushError\PushErrorCollection;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\Support\Result;

final readonly class GetReceiptsResult extends Result
{
    /**
     * @param PushReceiptCollection $receipts The resulting receipts
     * @param PushErrorCollection   $errors Any request-level errors encountered while fetching receipts
     */
    public function __construct(public PushReceiptCollection $receipts, PushErrorCollection $errors)
    {
        parent::__construct($errors);
    }

    /**
     * Whether any receipts were returned
     */
    public function hasReceipts(): bool
    {
        return $this->receipts->count() > 0;
    }

    /**
     * Whether at least one receipt was successful
     */
    public function hasSuccessfulReceipts(): bool
    {
        foreach ($this->receipts as $receipt) {
            if ($receipt->isSuccessful()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether at least one receipt failed
     */
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
