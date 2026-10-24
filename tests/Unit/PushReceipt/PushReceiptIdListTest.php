<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushReceipt;

use Dru1x\ExpoPush\PushReceipt\PushReceiptIdList;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;
use stdClass;

class PushReceiptIdListTest extends TestCase
{
    #[Test]
    public function add_appends_receipt_id_to_collection(): void
    {
        $collection = new PushReceiptIdList(['XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX']);

        $collection->add('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $this->assertCount(2, $collection);

        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $collection->get(0));
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $collection->get(1));
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ]);

        foreach ($collection as $receipt) {
            $this->assertIsString($receipt);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_receipt_id_exists(): void
    {
        $receipt1 = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
        $receipt2 = 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY';
        $receipt3 = 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ';

        $collection = new PushReceiptIdList([$receipt1, $receipt2, $receipt3]);

        $this->assertTrue($collection->contains($receipt1));
        $this->assertTrue($collection->contains($receipt2));
    }

    #[Test]
    public function contains_returns_false_when_push_receipt_id_doesnt_exist(): void
    {
        $receipt1 = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
        $receipt2 = 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY';
        $receipt3 = 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ';

        $collection = new PushReceiptIdList([$receipt1, $receipt2]);

        $this->assertFalse($collection->contains($receipt3));
    }

    #[Test]
    public function get_returns_correct_push_receipt_id(): void
    {
        $receipt1 = 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX';
        $receipt2 = 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY';

        $collection = new PushReceiptIdList([$receipt1, $receipt2]);

        $receipt = $collection->get(1);

        $this->assertEquals($receipt2, $receipt);
    }

    #[Test]
    public function get_returns_null_if_push_receipt_id_not_found(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        ]);

        $receipt = $collection->get(99);

        $this->assertNull($receipt);
    }

    #[Test]
    public function count_returns_correct_push_receipt_id_count(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ]);

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
        ]);

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function filter_returns_correctly_filtered_collection(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
        ]);

        $filteredCollection = $collection->filter(
            fn(string $receiptId) => $receiptId !== 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        );

        $this->assertCount(5, $filteredCollection);
        $this->assertSame(range(0, 4), array_keys($filteredCollection->toArray()));
        $this->assertSame($collection->get(2), $filteredCollection->get(1));
    }

    #[Test]
    public function filter_does_not_affect_original_collection(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
        ]);

        $collection->filter(
            fn(string $receiptId) => $receiptId !== 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        );

        $this->assertCount(6, $collection);
    }

    #[Test]
    public function to_array_returns_push_receipt_id_array(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ]);

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $receipt) {
            $this->assertIsString($receipt);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ]);

        $expectedJson = <<<JSON
            [
              "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
              "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
              "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ"
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ]);

        $expectedJson = <<<JSON
            [
              "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
              "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
              "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ"
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }

    #[Test]
    public function merge_returns_a_new_list_with_values_from_both_lists(): void
    {
        $collection = new PushReceiptIdList([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        ]);

        $merged = $collection->merge(new PushReceiptIdList([
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
        ]));

        $this->assertSame([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
        ], $merged->toArray());

        $this->assertCount(2, $collection);
    }

    #[Test]
    public function can_create_an_iterator_from_a_collection(): void
    {
        $collection = PushReceiptIdList::make([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        ]);

        $iterator = $collection->getIterator();

        $this->assertInstanceOf(
            Traversable::class,
            $iterator,
        );

        $results = iterator_to_array($iterator);

        $this->assertSame([
            'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        ], $results);
    }

    #[Test]
    public function can_check_if_a_collection_is_empty(): void
    {
        $empty = new PushReceiptIdList();
        $notEmpty = new PushReceiptIdList(['XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX']);

        $this->assertTrue(
            $empty->isEmpty(),
        );

        $this->assertFalse(
            $notEmpty->isEmpty(),
        );
    }

    #[Test]
    #[DataProvider('invalidReceiptIdProvider')]
    public function add_throws_exception_for_invalid_receipt_id(mixed $receiptId): void
    {
        $collection = new PushReceiptIdList();

        $this->expectException(InvalidArgumentException::class);

        $collection->add($receiptId);
    }

    #[Test]
    #[DataProvider('invalidReceiptIdProvider')]
    public function constructor_throws_exception_for_invalid_receipt_id(mixed $receiptId): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PushReceiptIdList(['XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $receiptId]);
    }

    public static function invalidReceiptIdProvider(): array
    {
        return [
            'empty string'      => [''],
            'whitespace string' => ['   '],
            'integer'           => [123],
            'null'              => [null],
            'object'            => [new stdClass()],
        ];
    }
}
