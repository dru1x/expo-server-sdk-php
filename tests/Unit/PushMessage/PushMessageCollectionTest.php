<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushMessage;

use ArrayIterator;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\PushMessageCollection;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;
use ValueError;

class PushMessageCollectionTest extends TestCase
{
    #[Test]
    public function add_appends_message_to_collection(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(
                to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                title: 'Test Notification 1'
            )
        );

        $collection->add(new PushMessage(
            to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            title: 'Test Notification 2'
        ));

        $this->assertCount(2, $collection);

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $collection->get(0)->to->toString());
        $this->assertEquals('Test Notification 1', $collection->get(0)->title);

        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $collection->get(1)->to->toString());
        $this->assertEquals('Test Notification 2', $collection->get(1)->title);
    }

    #[Test]
    public function set_inserts_message_to_collection_at_index(): void
    {
        $collection = new PushMessageCollection();

        $collection->set(9, new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification 9'
        ));

        $this->assertCount(1, $collection);

        $this->assertNull($collection->get(0));

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $collection->get(9)->to->toString());
        $this->assertEquals('Test Notification 9', $collection->get(9)->title);
    }

    #[Test]
    public function set_replaces_message_in_collection_at_index(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(
                to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                title: 'Test Notification 1'
            )
        );

        $collection->set(0, new PushMessage(
            to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            title: 'Test Notification 2'
        ));

        $this->assertCount(1, $collection);

        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $collection->get(0)->to->toString());
        $this->assertEquals('Test Notification 2', $collection->get(0)->title);
    }

    #[Test]
    public function notification_count_correctly_counts_resultant_notifications(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                new PushToken('ExponentPushToken[dddddddddddddddddddddd]'),
                new PushToken('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]'),
                new PushToken('ExponentPushToken[ffffffffffffffffffffff]'),
                new PushToken('ExponentPushToken[gggggggggggggggggggggg]'),
                new PushToken('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]'),
            ), title: 'Test Notification 6'),
        );

        $count = $collection->notificationCount();

        $this->assertEquals(11, $count);
    }

    #[Test]
    public function chunk_by_notifications_correctly_splits_up_push_messages(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                new PushToken('ExponentPushToken[dddddddddddddddddddddd]'),
                new PushToken('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]'),
                new PushToken('ExponentPushToken[ffffffffffffffffffffff]'),
                new PushToken('ExponentPushToken[gggggggggggggggggggggg]'),
                new PushToken('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]'),
            ), title: 'Test Notification 6'),
        );

        $chunks = $collection->chunkByNotifications(3);

        $this->assertCount(4, $chunks);

        $this->assertCount(3, $chunks[0]);
        $this->assertEquals('Test Notification 1', $chunks[0]->get(0)->title);
        $this->assertEquals('Test Notification 2', $chunks[0]->get(1)->title);
        $this->assertEquals('Test Notification 3', $chunks[0]->get(2)->title);

        $this->assertCount(3, $chunks[1]);
        $this->assertEquals('Test Notification 4', $chunks[1]->get(0)->title);
        $this->assertEquals('Test Notification 5', $chunks[1]->get(1)->title);
        $this->assertEquals('Test Notification 6', $chunks[1]->get(2)->title);

        $this->assertCount(1, $chunks[2]);
        $this->assertEquals('Test Notification 6', $chunks[2]->get(0)->title);
        $this->assertCount(3, $chunks[2]->get(0)->to);

        $this->assertCount(1, $chunks[3]);
        $this->assertEquals('Test Notification 6', $chunks[3]->get(0)->title);
        $this->assertCount(2, $chunks[3]->get(0)->to);
    }

    #[Test]
    public function chunk_by_notifications_returns_empty_array_for_empty_collection(): void
    {
        $collection = new PushMessageCollection();

        $chunks = $collection->chunkByNotifications(3);

        $this->assertEmpty($chunks);
    }

    #[Test]
    public function chunk_by_notifications_does_not_split_multi_token_message_that_fits_in_chunk(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            ), title: 'Test Notification'),
        );

        $chunks = $collection->chunkByNotifications(3);

        $this->assertCount(1, $chunks);
        $this->assertCount(1, $chunks[0]);
        $this->assertCount(3, $chunks[0]->get(0)->to);
    }

    #[Test]
    public function chunk_by_notifications_splits_standalone_multi_token_message_across_chunks(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
            ), title: 'Test Notification'),
        );

        $chunks = $collection->chunkByNotifications(2);

        $this->assertCount(3, $chunks);

        $this->assertCount(1, $chunks[0]);
        $this->assertCount(2, $chunks[0]->get(0)->to);

        $this->assertCount(1, $chunks[1]);
        $this->assertCount(2, $chunks[1]->get(0)->to);

        $this->assertCount(1, $chunks[2]);
        $this->assertCount(1, $chunks[2]->get(0)->to);
    }

    #[Test]
    public function chunk_by_notifications_preserves_token_order_when_splitting(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
            ), title: 'Test Notification'),
        );

        $chunks = $collection->chunkByNotifications(2);

        $this->assertCount(2, $chunks);

        $firstChunkTokens = $chunks[0]->get(0)->to;
        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $firstChunkTokens->get(0)->toString());
        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $firstChunkTokens->get(1)->toString());

        $secondChunkTokens = $chunks[1]->get(0)->to;
        $this->assertEquals('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]', $secondChunkTokens->get(0)->toString());
        $this->assertEquals('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]', $secondChunkTokens->get(1)->toString());
    }

    #[Test]
    public function chunk_by_notifications_with_size_one_puts_each_token_in_its_own_chunk(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            ), title: 'Test Notification'),
        );

        $chunks = $collection->chunkByNotifications(1);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(1, $chunk);
            $this->assertCount(1, $chunk->get(0)->to);
        }
    }

    #[Test]
    public function chunk_by_notifications_throws_exception_if_size_is_less_than_one(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
        );

        $this->expectException(ValueError::class);

        $collection->chunkByNotifications(0);
    }

    #[Test]
    public function filter_returns_correctly_filtered_collection(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
        );

        $filteredCollection = $collection->filter(
            fn(PushMessage $message) => $message->to->value !== 'ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'
        );

        $this->assertCount(4, $filteredCollection);
        $this->assertNull($filteredCollection->get(3));
    }

    #[Test]
    public function filter_does_not_affect_original_collection(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
        );

        $collection->filter(
            fn(PushMessage $message) => $message->to->value !== 'ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'
        );

        $this->assertCount(5, $collection);
    }

    #[Test]
    public function get_push_tokens_returns_correctly_ordered_push_token_collection(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushTokenCollection(
                new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                new PushToken('ExponentPushToken[dddddddddddddddddddddd]'),
                new PushToken('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]'),
                new PushToken('ExponentPushToken[ffffffffffffffffffffff]'),
                new PushToken('ExponentPushToken[gggggggggggggggggggggg]'),
                new PushToken('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]'),
            ), title: 'Test Notification 6'),
        );

        $tokens = $collection->getTokens();

        $this->assertCount(11, $tokens);

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $tokens->get(0)->toString());
        $this->assertEquals('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]', $tokens->get(1)->toString());
        $this->assertEquals('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]', $tokens->get(2)->toString());
        $this->assertEquals('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]', $tokens->get(3)->toString());
        $this->assertEquals('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]', $tokens->get(4)->toString());
        $this->assertEquals('ExponentPushToken[cccccccccccccccccccccc]', $tokens->get(5)->toString());
        $this->assertEquals('ExponentPushToken[dddddddddddddddddddddd]', $tokens->get(6)->toString());
        $this->assertEquals('ExponentPushToken[eeeeeeeeeeeeeeeeeeeeee]', $tokens->get(7)->toString());
        $this->assertEquals('ExponentPushToken[ffffffffffffffffffffff]', $tokens->get(8)->toString());
        $this->assertEquals('ExponentPushToken[gggggggggggggggggggggg]', $tokens->get(9)->toString());
        $this->assertEquals('ExponentPushToken[hhhhhhhhhhhhhhhhhhhhhh]', $tokens->get(10)->toString());
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        foreach ($collection as $message) {
            $this->assertInstanceOf(PushMessage::class, $message);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_message_exists(): void
    {
        $message1 = new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1');
        $message2 = new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2');

        $collection = new PushMessageCollection($message1, $message2);

        $this->assertTrue($collection->contains($message1));
        $this->assertTrue($collection->contains($message2));
    }

    #[Test]
    public function contains_returns_false_when_push_message_doesnt_exist(): void
    {
        $message1 = new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1');
        $message2 = new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2');
        $message3 = new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3');

        $collection = new PushMessageCollection($message1, $message2);

        $this->assertFalse($collection->contains($message3));
    }

    #[Test]
    public function get_returns_correct_push_message(): void
    {
        $message1 = new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1');
        $message2 = new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2');

        $collection = new PushMessageCollection($message1, $message2);

        $message = $collection->get(1);

        $this->assertEquals($message2, $message);
    }

    #[Test]
    public function get_returns_null_if_push_message_not_found(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
        );

        $message = $collection->get(99);

        $this->assertNull($message);
    }

    #[Test]
    public function count_returns_correct_push_message_count(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
            new PushMessage(to: new PushToken('ExponentPushToken[cccccccccccccccccccccc]'), title: 'Test Notification 6'),
        );

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function values_returns_collection_with_consecutive_keys(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(
                to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                title: 'Test Notification 1'
            ),
        );

        $collection->set(9, new PushMessage(
            to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            title: 'Test Notification 2'
        ));

        $newCollection = $collection->values();

        $this->assertIsList($newCollection->toArray());
    }

    #[Test]
    public function to_array_returns_push_message_array(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $message) {
            $this->assertInstanceOf(PushMessage::class, $message);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $expectedJson = <<<JSON
[
  {
    "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
    "title": "Test Notification 1"
  },
  {
    "to": "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]",
    "title": "Test Notification 2"
  },
  {
    "to": "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]",
    "title": "Test Notification 3"
  }
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
        );

        $expectedJson = <<<JSON
[
  {
    "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
    "title": "Test Notification 1"
  },
  {
    "to": "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]",
    "title": "Test Notification 2"
  },
  {
    "to": "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]",
    "title": "Test Notification 3"
  }
]
JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }

    #[Test]
    public function can_merge_a_collection_with_provided_iterables(): void
    {
        $collection = PushMessageCollection::make()
            ->add(new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'))
            ->merge(
                new ArrayIterator([
                    new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
                    new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
                ]),
                new ArrayIterator([
                    new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
                    new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
                ])
            );

        $this->assertEquals([
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
        ], $collection->all());
    }

    #[Test]
    public function can_retrieve_all_items_from_a_collection(): void
    {
        $collection = PushMessageCollection::make(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
        );

        $this->assertEquals([
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
        ], $collection->all());
    }

    #[Test]
    public function can_create_an_iterator_from_a_collection(): void
    {
        $collection = PushMessageCollection::make(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
        );

        $iterator = $collection->getIterator();

        $this->assertInstanceOf(
            Traversable::class,
            $iterator,
        );

        $results = iterator_to_array($iterator);

        $this->assertEquals([
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
        ], $results);
    }

    #[Test]
    public function can_check_if_a_collection_is_empty(): void
    {
        $empty = new PushMessageCollection;

        $notEmpty = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
        );

        $this->assertTrue(
            $empty->isEmpty()
        );

        $this->assertFalse(
            $notEmpty->isEmpty()
        );
    }

    #[Test]
    public function reject_returns_correctly_filtered_collection(): void
    {
        $collection = new PushMessageCollection(
            new PushMessage(to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'), title: 'Test Notification 1'),
            new PushMessage(to: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'), title: 'Test Notification 2'),
            new PushMessage(to: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'), title: 'Test Notification 3'),
            new PushMessage(to: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'), title: 'Test Notification 4'),
            new PushMessage(to: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'), title: 'Test Notification 5'),
        );

        $filteredCollection = $collection->reject(
            fn(PushMessage $message) => $message->title === 'Test Notification 4'
        );

        $this->assertCount(4, $filteredCollection);
        $this->assertNull($filteredCollection->get(3));
    }
}
