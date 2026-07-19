<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushReceipt;

use ArrayIterator;
use Dru1x\ExpoPush\PushReceipt\PushReceipt;
use Dru1x\ExpoPush\PushReceipt\PushReceiptCollection;
use Dru1x\ExpoPush\PushReceipt\SuccessfulPushReceipt;
use Dru1x\ExpoPush\Support\PushStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;

class PushReceiptCollectionTest extends TestCase
{
    #[Test]
    public function add_appends_receipt_to_collection(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        );

        $collection->add(
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        );

        $this->assertCount(2, $collection);

        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $collection->get(0)->id);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $collection->get(1)->id);
    }

    #[Test]
    public function set_inserts_receipt_to_collection_at_index(): void
    {
        $collection = new PushReceiptCollection();

        $collection->set(9, new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'));

        $this->assertCount(1, $collection);
        $this->assertNull($collection->get(0));
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $collection->get(9)->id);
    }

    #[Test]
    public function set_replaces_receipt_in_collection_at_index(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        );

        $collection->set(0, new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'));

        $this->assertCount(1, $collection);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $collection->get(0)->id);
    }

    #[Test]
    public function get_by_id_returns_correct_receipt(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $receipt = $collection->getById('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $this->assertInstanceOf(PushReceipt::class, $receipt);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $receipt->id);
        $this->assertEquals(PushStatus::Ok, $receipt->status);
    }

    #[Test]
    public function get_by_id_returns_null_if_receipt_not_found(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $receipt = $collection->getById('ABCDEFGH-ABCDEF-ABCDEF-ABCDEF-ABCDEFGHIJKL');
        ;

        $this->assertNull($receipt);
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        foreach ($collection as $receipt) {
            $this->assertInstanceOf(PushReceipt::class, $receipt);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_receipt_exists(): void
    {
        $receipt1 = new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $receipt2 = new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');
        $receipt3 = new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ');

        $collection = new PushReceiptCollection($receipt1, $receipt2, $receipt3);

        $this->assertTrue($collection->contains($receipt1));
        $this->assertTrue($collection->contains($receipt2));
    }

    #[Test]
    public function contains_returns_false_when_push_receipt_doesnt_exist(): void
    {
        $receipt1 = new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $receipt2 = new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');
        $receipt3 = new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ');

        $collection = new PushReceiptCollection($receipt1, $receipt2);

        $this->assertFalse($collection->contains($receipt3));
    }

    #[Test]
    public function get_returns_correct_push_receipt(): void
    {
        $receipt1 = new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX');
        $receipt2 = new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY');

        $collection = new PushReceiptCollection($receipt1, $receipt2);

        $receipt = $collection->get(1);

        $this->assertEquals($receipt2, $receipt);
    }

    #[Test]
    public function get_returns_null_if_push_receipt_not_found(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        );

        $receipt = $collection->get(99);

        $this->assertNull($receipt);
    }

    #[Test]
    public function count_returns_correct_push_receipt_count(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
            new SuccessfulPushReceipt(id: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new SuccessfulPushReceipt(id: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            new SuccessfulPushReceipt(id: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
        );

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function filter_returns_correctly_filtered_collection(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
            new SuccessfulPushReceipt(id: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new SuccessfulPushReceipt(id: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            new SuccessfulPushReceipt(id: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
        );

        $filteredCollection = $collection->filter(
            fn(SuccessfulPushReceipt $receipt) => $receipt->id !== 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );

        $this->assertCount(5, $filteredCollection);
        $this->assertNull($filteredCollection->get(0));
    }

    #[Test]
    public function filter_does_not_affect_original_collection(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
            new SuccessfulPushReceipt(id: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new SuccessfulPushReceipt(id: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            new SuccessfulPushReceipt(id: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
        );

        $collection->filter(
            fn(SuccessfulPushReceipt $receipt) => $receipt->id !== 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );

        $this->assertCount(6, $collection);
    }

    #[Test]
    public function values_returns_collection_with_consecutive_keys(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        );

        $collection->set(9, new SuccessfulPushReceipt(
            id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        ));

        $newCollection = $collection->values();

        $this->assertIsList($newCollection->toArray());
    }

    #[Test]
    public function to_array_returns_push_receipt_array(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $receipt) {
            $this->assertInstanceOf(PushReceipt::class, $receipt);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $expectedJson = <<<JSON
            [
              {
                "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "status": "ok"
              },
              {
                "id": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
                "status": "ok"
              },
              {
                "id": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ",
                "status": "ok"
              }
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
        );

        $expectedJson = <<<JSON
            [
              {
                "id": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "status": "ok"
              },
              {
                "id": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
                "status": "ok"
              },
              {
                "id": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ",
                "status": "ok"
              }
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }

    #[Test]
    public function can_merge_a_collection_with_provided_iterables(): void
    {
        $collection = PushReceiptCollection::make()
            ->add(new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'))
            ->merge(
                new ArrayIterator([
                    new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
                    new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
                ]),
                new ArrayIterator([
                    new SuccessfulPushReceipt(id: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
                    new SuccessfulPushReceipt(id: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
                ]),
            );

        $this->assertEquals([
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
            new SuccessfulPushReceipt(id: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new SuccessfulPushReceipt(id: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
        ], $collection->all());
    }

    #[Test]
    public function can_retrieve_all_items_from_a_collection(): void
    {
        $collection = PushReceiptCollection::make(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        );

        $this->assertEquals([
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ], $collection->all());
    }

    #[Test]
    public function can_create_an_iterator_from_a_collection(): void
    {
        $collection = PushReceiptCollection::make(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        );

        $iterator = $collection->getIterator();

        $this->assertInstanceOf(
            Traversable::class,
            $iterator,
        );

        $results = iterator_to_array($iterator);

        $this->assertEquals([
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
        ], $results);
    }

    #[Test]
    public function can_check_if_a_collection_is_empty(): void
    {
        $empty = new PushReceiptCollection();

        $notEmpty = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
        );

        $this->assertTrue(
            $empty->isEmpty(),
        );

        $this->assertFalse(
            $notEmpty->isEmpty(),
        );
    }

    #[Test]
    public function reject_returns_correctly_filtered_collection(): void
    {
        $collection = new PushReceiptCollection(
            new SuccessfulPushReceipt(id: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX'),
            new SuccessfulPushReceipt(id: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY'),
            new SuccessfulPushReceipt(id: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ'),
            new SuccessfulPushReceipt(id: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new SuccessfulPushReceipt(id: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'),
            new SuccessfulPushReceipt(id: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC'),
        );

        $filteredCollection = $collection->reject(
            fn(SuccessfulPushReceipt $receipt) => $receipt->id === 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        );

        $this->assertCount(5, $filteredCollection);
        $this->assertNull($filteredCollection->get(1));
    }
}
