<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushTicket;

use Dru1x\ExpoPush\PushTicket\PushTicketMap;
use Dru1x\ExpoPush\PushTicket\SuccessfulPushTicket;
use Dru1x\ExpoPush\PushToken\PushToken;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

class PushTicketMapTest extends TestCase
{
    #[Test]
    public function put_adds_ticket_at_the_given_index(): void
    {
        $map = new PushTicketMap();

        $map->put(9, $this->makeTicket('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'));

        $this->assertCount(1, $map);
        $this->assertNull($map->get(0));
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $map->get(9)->receiptId);
    }

    #[Test]
    public function put_replaces_ticket_at_the_given_index(): void
    {
        $map = new PushTicketMap([
            0 => $this->makeTicket('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        ]);

        $map->put(0, $this->makeTicket('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'));

        $this->assertCount(1, $map);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $map->get(0)->receiptId);
    }

    #[Test]
    public function index_gaps_are_preserved(): void
    {
        $map = new PushTicketMap([
            0  => $this->makeTicket('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            1  => $this->makeTicket('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            10 => $this->makeTicket('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        ]);

        $this->assertSame([0, 1, 10], array_keys($map->toArray()));
        $this->assertSame([0, 10], array_keys($map->filter(fn($ticket, int $index) => $index !== 1)->toArray()));
    }

    #[Test]
    public function merge_preserves_indexes(): void
    {
        $map = new PushTicketMap([
            0 => $this->makeTicket('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            1 => $this->makeTicket('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ]);

        $merged = $map->merge(new PushTicketMap([
            1    => $this->makeTicket('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            1000 => $this->makeTicket('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        ]));

        $this->assertSame([0, 1, 1000], array_keys($merged->toArray()));
        $this->assertEquals('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $merged->get(1)->receiptId);
        $this->assertEquals('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', $merged->get(1000)->receiptId);
    }

    #[Test]
    #[DataProvider('invalidKeyProvider')]
    public function put_throws_exception_for_invalid_key(int|string $key): void
    {
        $map = new PushTicketMap();

        $this->expectException(InvalidArgumentException::class);

        $map->put($key, $this->makeTicket('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'));
    }

    public static function invalidKeyProvider(): array
    {
        return [
            'negative integer' => [-1],
            'string'           => ['foo'],
        ];
    }

    #[Test]
    public function put_throws_exception_for_invalid_value(): void
    {
        $map = new PushTicketMap();

        $this->expectException(InvalidArgumentException::class);

        $map->put(0, new stdClass());
    }

    // Helpers ----

    protected function makeTicket(string $receiptId): SuccessfulPushTicket
    {
        return new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            receiptId: $receiptId,
        );
    }
}
