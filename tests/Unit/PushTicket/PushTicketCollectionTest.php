<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushTicket;

use ArrayIterator;
use Dru1x\ExpoPush\PushTicket\PushTicket;
use Dru1x\ExpoPush\PushTicket\PushTicketCollection;
use Dru1x\ExpoPush\PushTicket\SuccessfulPushTicket;
use Dru1x\ExpoPush\PushToken\PushToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;

class PushTicketCollectionTest extends TestCase
{
    #[Test]
    public function add_appends_ticket_to_collection(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
        );

        $collection->add(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
        );

        $this->assertCount(2, $collection);

        $pushTicket1 = $collection->get(0);
        $this->assertInstanceOf(SuccessfulPushTicket::class, $pushTicket1);
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $pushTicket1->receiptId);

        $pushTicket2 = $collection->get(1);
        $this->assertInstanceOf(SuccessfulPushTicket::class, $pushTicket2);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $pushTicket2->receiptId);
    }

    #[Test]
    public function set_inserts_ticket_to_collection_at_index(): void
    {
        $collection = new PushTicketCollection();

        $collection->set(
            9,
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
        );

        $this->assertCount(1, $collection);
        $this->assertNull($collection->get(0));

        $pushTicket = $collection->get(9);
        $this->assertInstanceOf(SuccessfulPushTicket::class, $pushTicket);
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $pushTicket->receiptId);
    }

    #[Test]
    public function set_replaces_ticket_in_collection_at_index(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
        );

        $collection->set(
            0,
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
        );

        $this->assertCount(1, $collection);

        $pushTicket = $collection->get(0);
        $this->assertInstanceOf(SuccessfulPushTicket::class, $pushTicket);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $pushTicket->receiptId);
    }

    #[Test]
    public function merge_returns_single_combined_collection(): void
    {
        $collection1 = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
        );

        $collection2 = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                receiptId: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
            ),
        );

        $mergedCollection = $collection1->merge($collection2);

        $this->assertCount(6, $mergedCollection);
        $this->assertEquals('XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX', $mergedCollection->get(0)->receiptId);
        $this->assertEquals('YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY', $mergedCollection->get(1)->receiptId);
        $this->assertEquals('ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ', $mergedCollection->get(2)->receiptId);
        $this->assertEquals('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $mergedCollection->get(3)->receiptId);
        $this->assertEquals('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $mergedCollection->get(4)->receiptId);
        $this->assertEquals('CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC', $mergedCollection->get(5)->receiptId);
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
        );

        foreach ($collection as $ticket) {
            $this->assertInstanceOf(PushTicket::class, $ticket);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_ticket_exists(): void
    {
        $ticket1 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );
        $ticket2 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        );
        $ticket3 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $collection = new PushTicketCollection($ticket1, $ticket2, $ticket3);

        $this->assertTrue($collection->contains($ticket1));
        $this->assertTrue($collection->contains($ticket2));
    }

    #[Test]
    public function contains_returns_false_when_push_ticket_doesnt_exist(): void
    {
        $ticket1 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );
        $ticket2 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        );
        $ticket3 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $collection = new PushTicketCollection($ticket1, $ticket2);

        $this->assertFalse($collection->contains($ticket3));
    }

    #[Test]
    public function get_returns_correct_push_ticket(): void
    {
        $ticket1 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
        );
        $ticket2 = new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        );

        $collection = new PushTicketCollection($ticket1, $ticket2);

        $ticket = $collection->get(1);

        $this->assertEquals($ticket2, $ticket);
    }

    #[Test]
    public function get_returns_null_if_push_ticket_not_found(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
        );

        $ticket = $collection->get(99);

        $this->assertNull($ticket);
    }

    #[Test]
    public function count_returns_correct_push_ticket_count(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
        );

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                receiptId: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
            ),
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
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                receiptId: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
            ),
        );

        $filteredCollection = $collection->filter(
            fn(SuccessfulPushTicket $ticket) => $ticket->receiptId !== 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $this->assertCount(5, $filteredCollection);
        $this->assertNull($filteredCollection->get(2));
    }

    #[Test]
    public function filter_does_not_affect_original_collection(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[cccccccccccccccccccccc]'),
                receiptId: 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
            ),
        );

        $collection->filter(
            fn(SuccessfulPushTicket $ticket) => $ticket->receiptId !== 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
        );

        $this->assertCount(6, $collection);
    }

    #[Test]
    public function values_returns_collection_with_consecutive_keys(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
        );

        $collection->set(9, new SuccessfulPushTicket(
            token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
            receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
        ));

        $newCollection = $collection->values();

        $this->assertIsList($newCollection->toArray());
    }

    #[Test]
    public function to_array_returns_push_ticket_array(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
        );

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $ticket) {
            $this->assertInstanceOf(PushTicket::class, $ticket);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
        );

        $expectedJson = <<<JSON
            [
              {
                "receiptId": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "status": "ok",
                "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
              },
              {
                "receiptId": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
                "status": "ok",
                "token": "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]"
              },
              {
                "receiptId": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ",
                "status": "ok",
                "token": "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]"
              }
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
        );

        $expectedJson = <<<JSON
            [
              {
                "receiptId": "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX",
                "status": "ok",
                "token": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
              },
              {
                "receiptId": "YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY",
                "status": "ok",
                "token": "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]"
              },
              {
                "receiptId": "ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ",
                "status": "ok",
                "token": "ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]"
              }
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }

    #[Test]
    public function can_merge_a_collection_with_provided_iterables(): void
    {
        $collection = PushTicketCollection::make()
            ->add(new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ))
            ->merge(
                new ArrayIterator([
                    new SuccessfulPushTicket(
                        token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                        receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
                    ),
                    new SuccessfulPushTicket(
                        token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                        receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
                    ),
                ]),
                new ArrayIterator([
                    new SuccessfulPushTicket(
                        token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                        receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAA',
                    ),
                    new SuccessfulPushTicket(
                        token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                        receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
                    ),
                ]),
            );

        $this->assertEquals([
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
            ),
        ], $collection->all());
    }

    #[Test]
    public function can_retrieve_all_items_from_a_collection(): void
    {
        $collection = PushTicketCollection::make(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
            ),
        );

        $this->assertEquals([
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
            ),
        ], $collection->all());
    }

    #[Test]
    public function can_create_an_iterator_from_a_collection(): void
    {
        $collection = PushTicketCollection::make(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
            ),
        );

        $iterator = $collection->getIterator();

        $this->assertInstanceOf(
            Traversable::class,
            $iterator,
        );

        $results = iterator_to_array($iterator);

        $this->assertEquals([
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
            ),
        ], $results);
    }

    #[Test]
    public function can_check_if_a_collection_is_empty(): void
    {
        $empty = new PushTicketCollection();

        $notEmpty = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
            ),
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
        $collection = new PushTicketCollection(
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                receiptId: 'XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                receiptId: 'YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
                receiptId: 'ZZZZZZZZ-ZZZZ-ZZZZ-ZZZZ-ZZZZZZZZZZZZ',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[aaaaaaaaaaaaaaaaaaaaaa]'),
                receiptId: 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAA',
            ),
            new SuccessfulPushTicket(
                token: new PushToken('ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]'),
                receiptId: 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBB',
            ),
        );

        $filteredCollection = $collection->reject(
            fn(SuccessfulPushTicket $ticket) => $ticket->token->value === 'ExponentPushToken[bbbbbbbbbbbbbbbbbbbbbb]',
        );

        $this->assertCount(4, $filteredCollection);
        $this->assertNull($filteredCollection->get(4));
    }
}
