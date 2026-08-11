<?php

namespace Dru1x\ExpoPush\Tests\Unit\Support;

use Dru1x\ExpoPush\Support\RetriesRequests;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RetriesRequestsTest extends TestCase
{
    #[Test]
    public function exponential_backoff_delay_doubles_with_each_attempt(): void
    {
        $calculator = new RetryDelayCalculator();
        $interval   = 5;

        foreach ([1, 2, 3, 4] as $attempt) {
            $expectedDelayMs = $interval * (2 ** ($attempt - 1));

            $this->assertSame($expectedDelayMs, $calculator->delay($attempt, $interval, true));
        }
    }

    #[Test]
    public function delay_stays_constant_across_attempts_when_exponential_backoff_is_disabled(): void
    {
        $calculator = new RetryDelayCalculator();
        $interval   = 5;

        foreach ([1, 2, 3] as $attempt) {
            $this->assertSame($interval, $calculator->delay($attempt, $interval, false));
        }
    }
}

// Helper Classes ----

class RetryDelayCalculator
{
    use RetriesRequests;

    public function delay(int $attempt, int $interval, bool $useExponentialBackoff): int
    {
        return $this->calculateRetryDelay($attempt, $interval, $useExponentialBackoff);
    }
}
