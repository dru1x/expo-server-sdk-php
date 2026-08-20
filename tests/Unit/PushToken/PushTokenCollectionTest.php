<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushToken;

use ArrayIterator;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;
use TypeError;

class PushTokenCollectionTest extends TestCase
{
    #[Test]
    public function add_appends_token_to_collection(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
        );

        $collection->add(
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
        );

        $this->assertCount(2, $collection);

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $collection->get(0)->toString());
        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $collection->get(1)->toString());
    }

    #[Test]
    public function set_inserts_token_to_collection_at_index(): void
    {
        $collection = new PushTokenCollection();

        $collection->set(9, new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'));

        $this->assertCount(1, $collection);
        $this->assertNull($collection->get(0));
        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $collection->get(9)->toString());
    }

    #[Test]
    public function set_replaces_token_in_collection_at_index(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
        );

        $collection->set(0, new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'));

        $this->assertCount(1, $collection);
        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $collection->get(0)->toString());
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        foreach ($collection as $token) {
            $this->assertInstanceOf(PushToken::class, $token);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_token_exists(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');
        $token3 = new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]');

        $collection = new PushTokenCollection($token1, $token2, $token3);

        $this->assertTrue($collection->contains($token1));
        $this->assertTrue($collection->contains($token2));
    }

    #[Test]
    public function contains_returns_false_when_push_token_doesnt_exist(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');
        $token3 = new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]');

        $collection = new PushTokenCollection($token1, $token2);

        $this->assertFalse($collection->contains($token3));
    }

    #[Test]
    public function get_returns_correct_push_token(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');

        $collection = new PushTokenCollection($token1, $token2);

        $token = $collection->get(1);

        $this->assertEquals($token2, $token);
    }

    #[Test]
    public function get_returns_null_if_push_token_not_found(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
        );

        $token = $collection->get(99);

        $this->assertNull($token);
    }

    #[Test]
    public function count_returns_correct_push_token_count(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function shift_with_negative_count_throws_exception(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
        );

        $this->expectException(InvalidArgumentException::class);

        $collection->shift(-1);
    }

    #[Test]
    public function shift_with_zero_count_returns_empty_collection_without_mutating_source(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');
        $token3 = new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]');

        $collection = new PushTokenCollection($token1, $token2, $token3);

        $shifted = $collection->shift(0);

        $this->assertTrue($shifted->isEmpty());

        $this->assertCount(3, $collection);
        $this->assertEquals($token1, $collection->get(0));
        $this->assertEquals($token2, $collection->get(1));
        $this->assertEquals($token3, $collection->get(2));
    }

    #[Test]
    public function shift_from_empty_collection_returns_empty_collection(): void
    {
        $collection = new PushTokenCollection();

        $shifted = $collection->shift();

        $this->assertTrue($shifted->isEmpty());
    }

    #[Test]
    public function shift_with_count_greater_than_available_returns_all_items(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');

        $collection = new PushTokenCollection($token1, $token2);

        $shifted = $collection->shift(5);

        $this->assertCount(2, $shifted);
        $this->assertEquals($token1, $shifted->get(0));
        $this->assertEquals($token2, $shifted->get(1));

        $this->assertTrue($collection->isEmpty());
    }

    #[Test]
    public function shift_removes_and_returns_leading_items_leaving_remainder_in_source(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');
        $token3 = new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]');
        $token4 = new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]');

        $collection = new PushTokenCollection($token1, $token2, $token3, $token4);

        $shifted = $collection->shift(2);

        $this->assertCount(2, $shifted);
        $this->assertEquals($token1, $shifted->get(0));
        $this->assertEquals($token2, $shifted->get(1));

        $this->assertCount(2, $collection);
        $this->assertEquals($token3, $collection->get(0));
        $this->assertEquals($token4, $collection->get(1));
    }

    #[Test]
    public function shift_with_default_count_removes_single_item(): void
    {
        $token1 = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $token2 = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');

        $collection = new PushTokenCollection($token1, $token2);

        $shifted = $collection->shift();

        $this->assertCount(1, $shifted);
        $this->assertEquals($token1, $shifted->get(0));

        $this->assertCount(1, $collection);
        $this->assertEquals($token2, $collection->get(0));
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
            new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
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
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
            new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
        );

        $filteredCollection = $collection->filter(
            fn(PushToken $token) => $token->value !== 'ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]',
        );

        $this->assertCount(5, $filteredCollection);
        $this->assertNull($filteredCollection->get(4));
    }

    #[Test]
    public function filter_does_not_affect_original_collection(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
            new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
        );

        $collection->filter(
            fn(PushToken $token) => $token->value !== 'ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]',
        );

        $this->assertCount(6, $collection);
    }

    #[Test]
    public function values_returns_collection_with_consecutive_keys(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
        );

        $collection->set(9, new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'));

        $newCollection = $collection->values();

        $this->assertIsList($newCollection->toArray());
    }

    #[Test]
    public function to_array_returns_push_token_array(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $token) {
            $this->assertInstanceOf(PushToken::class, $token);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $expectedJson = <<<JSON
            [
              "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
              "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]",
              "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]"
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
        );

        $expectedJson = <<<JSON
            [
              "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
              "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]",
              "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]"
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }

    #[Test]
    public function from_json_returns_instance(): void
    {
        $json = <<<JSON
            [
              "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
              "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]",
              "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]"
            ]
            JSON;

        $collection = PushTokenCollection::fromJson($json);

        $this->assertInstanceOf(PushTokenCollection::class, $collection);

        foreach ($collection as $token) {
            $this->assertInstanceOf(PushToken::class, $token);
        }
    }

    #[Test]
    public function from_json_with_null_throws_error(): void
    {
        $this->expectException(TypeError::class);

        PushTokenCollection::fromJson(null);
    }

    #[Test]
    public function can_merge_a_collection_with_provided_iterables(): void
    {
        $collection = PushTokenCollection::make()
            ->add(new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'))
            ->merge(
                new ArrayIterator([
                    new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                    new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                ]),
                new ArrayIterator([
                    new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                    new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                ]),
            );

        $this->assertEquals([
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
        ], $collection->all());
    }

    #[Test]
    public function can_retrieve_all_items_from_a_collection(): void
    {
        $collection = PushTokenCollection::make(
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
        );

        $this->assertEquals([
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
        ], $collection->all());
    }

    #[Test]
    public function can_create_an_iterator_from_a_collection(): void
    {
        $collection = PushTokenCollection::make(
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
        );

        $iterator = $collection->getIterator();

        $this->assertInstanceOf(
            Traversable::class,
            $iterator,
        );

        $results = iterator_to_array($iterator);

        $this->assertEquals([
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
        ], $results);
    }

    #[Test]
    public function can_check_if_a_collection_is_empty(): void
    {
        $empty = new PushTokenCollection();

        $notEmpty = new PushTokenCollection(
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
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
        $collection = new PushTokenCollection(
            new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
        );

        $filteredCollection = $collection->reject(
            fn(PushToken $token) => $token->value === 'ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]',
        );

        $this->assertCount(4, $filteredCollection);
        $this->assertNull($filteredCollection->get(2));
    }
}
