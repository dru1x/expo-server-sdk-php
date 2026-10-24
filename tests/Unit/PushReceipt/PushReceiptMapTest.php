<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushReceipt;

use Dru1x\ExpoPush\PushReceipt\FailedPushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptDetails;
use Dru1x\ExpoPush\PushReceipt\PushReceiptErrorCode;
use Dru1x\ExpoPush\PushReceipt\PushReceiptMap;
use Dru1x\ExpoPush\PushReceipt\SuccessfulPushReceipt;
use Dru1x\ExpoPush\Support\PushStatus;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

class PushReceiptMapTest extends TestCase
{
    #[Test]
    public function constructor_keys_receipts_by_the_given_keys(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ]);

        $this->assertCount(2, $map);
        $this->assertSame(
            ['XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'],
            array_keys($map->toArray()),
        );
    }

    #[Test]
    public function put_adds_receipt_at_the_given_id(): void
    {
        $map = new PushReceiptMap();

        $map->put('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'));

        $this->assertCount(1, $map);
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $map->get('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX')->id);
    }

    #[Test]
    public function put_replaces_receipt_with_the_same_id(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        ]);

        $map->put('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $this->makeFailedPushReceipt('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'));

        $this->assertCount(1, $map);
        $this->assertInstanceOf(FailedPushReceipt::class, $map->get('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'));
    }

    #[Test]
    public function put_all_adds_receipts_from_another_map(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        ]);

        $map->putAll(new PushReceiptMap([
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        ]));

        $this->assertCount(3, $map);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $map->get('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY')->id);
        $this->assertEquals('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', $map->get('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ')->id);
    }

    #[Test]
    public function get_returns_correct_push_receipt_by_id(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        ]);

        $receipt = $map->get('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $this->assertInstanceOf(PushReceipt::class, $receipt);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $receipt->id);
        $this->assertEquals(PushStatus::Ok, $receipt->status);
    }

    #[Test]
    public function get_returns_null_if_push_receipt_not_found(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        ]);

        $this->assertNull($map->get('ABCDEFGH-ABCDEF-ABCDEF-ABCDEF-ABCDEFGHIJKL'));
        $this->assertNull($map->get(0));
    }

    #[Test]
    public function map_is_iterable_by_receipt_id(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ]);

        $this->assertInstanceOf(Traversable::class, $map->getIterator());

        foreach ($map as $id => $receipt) {
            $this->assertInstanceOf(PushReceipt::class, $receipt);
            $this->assertSame($id, $receipt->id);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_receipt_exists(): void
    {
        $receipt1 = new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $receipt2 = new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $map = new PushReceiptMap([$receipt1->id => $receipt1, $receipt2->id => $receipt2]);

        $this->assertTrue($map->contains($receipt1));
        $this->assertTrue($map->contains($receipt2));
    }

    #[Test]
    public function contains_returns_false_when_push_receipt_doesnt_exist(): void
    {
        $receipt1 = new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $receipt2 = new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $map = new PushReceiptMap([$receipt1->id => $receipt1]);

        $this->assertFalse($map->contains($receipt2));
        $this->assertTrue($map->doesNotContain($receipt2));
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks_preserving_ids(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        ]);

        $chunks = $map->chunk(2);

        $this->assertCount(2, $chunks);
        $this->assertContainsOnlyInstancesOf(PushReceiptMap::class, $chunks);
        $this->assertSame(
            ['XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'],
            array_keys($chunks[0]->toArray()),
        );
        $this->assertSame(
            ['ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
            array_keys($chunks[1]->toArray()),
        );
    }

    #[Test]
    public function filter_returns_correctly_filtered_map_preserving_ids(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => $this->makeFailedPushReceipt('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        ]);

        $filtered = $map->filter(fn(PushReceipt $receipt) => $receipt->isSuccessful());

        $this->assertInstanceOf(PushReceiptMap::class, $filtered);
        $this->assertSame(
            ['XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'],
            array_keys($filtered->toArray()),
        );
    }

    #[Test]
    public function filter_does_not_affect_original_map(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ]);

        $map->filter(fn(PushReceipt $receipt) => false);

        $this->assertCount(2, $map);
    }

    #[Test]
    public function merge_returns_a_new_map_with_receipts_from_both_maps(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ]);

        $merged = $map->merge(new PushReceiptMap([
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => $this->makeFailedPushReceipt('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ' => new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        ]));

        $this->assertCount(3, $merged);
        $this->assertInstanceOf(FailedPushReceipt::class, $merged->get('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'));
        $this->assertCount(2, $map);
    }

    #[Test]
    public function to_array_returns_push_receipts_keyed_by_id(): void
    {
        $receipt1 = new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $receipt2 = new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $map = new PushReceiptMap([$receipt1->id => $receipt1, $receipt2->id => $receipt2]);

        $this->assertSame([$receipt1->id => $receipt1, $receipt2->id => $receipt2], $map->toArray());
    }

    #[Test]
    public function json_encode_returns_object_keyed_by_receipt_id(): void
    {
        $map = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY' => new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ]);

        $expectedJson = <<<JSON
            {
              "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX": {
                "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "status": "ok"
              },
              "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY": {
                "id": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
                "status": "ok"
              }
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($map));
        $this->assertJsonStringEqualsJsonString($expectedJson, $map->toJson());
    }

    #[Test]
    public function can_check_if_a_map_is_empty(): void
    {
        $empty    = new PushReceiptMap();
        $notEmpty = new PushReceiptMap([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX' => new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        ]);

        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($notEmpty->isEmpty());
        $this->assertTrue($notEmpty->isNotEmpty());
    }

    #[Test]
    #[DataProvider('invalidKeyProvider')]
    public function put_throws_exception_for_invalid_key(int|string $key): void
    {
        $map = new PushReceiptMap();

        $this->expectException(InvalidArgumentException::class);

        $map->put($key, new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'));
    }

    public static function invalidKeyProvider(): array
    {
        return [
            'empty string'      => [''],
            'whitespace string' => ['   '],
            'integer'           => [0],
        ];
    }

    #[Test]
    public function constructor_throws_exception_for_list_of_receipts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PushReceiptMap([
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        ]);
    }

    #[Test]
    public function put_throws_exception_for_invalid_value(): void
    {
        $map = new PushReceiptMap();

        $this->expectException(InvalidArgumentException::class);

        $map->put('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', new stdClass());
    }

    // Helpers ----

    protected function makeFailedPushReceipt(string $id): FailedPushReceipt
    {
        return new FailedPushReceipt(
            id: $id,
            message: 'Unknown error',
            details: new PushReceiptDetails(error: PushReceiptErrorCode::Unknown, expoPushToken: null),
        );
    }
}
