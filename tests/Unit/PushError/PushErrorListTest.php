<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushError;

use Dru1x\ExpoPush\PushError\PushError;
use Dru1x\ExpoPush\PushError\PushErrorCode;
use Dru1x\ExpoPush\PushError\PushErrorList;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;
use stdClass;

class PushErrorListTest extends TestCase
{
    #[Test]
    public function add_appends_message_to_collection(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
        ]);

        $collection->add(new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'));

        $this->assertCount(2, $collection);

        $this->assertEquals('FAILED', $collection->get(0)->code->value);
        $this->assertEquals('Push notifications failed to send', $collection->get(0)->message);

        $this->assertEquals('UNKNOWN', $collection->get(1)->code->value);
        $this->assertEquals('Unknown error', $collection->get(1)->message);
    }

    #[Test]
    public function collection_is_iterable(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
        ]);

        foreach ($collection as $error) {
            $this->assertInstanceOf(PushError::class, $error);
        }
    }

    #[Test]
    public function contains_returns_true_when_push_message_exists(): void
    {
        $error1 = new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send');
        $error2 = new PushError(code: PushErrorCode::Unknown, message: 'Unknown error');

        $collection = new PushErrorList([$error1, $error2]);

        $this->assertTrue($collection->contains($error1));
        $this->assertTrue($collection->contains($error2));
    }

    #[Test]
    public function contains_returns_false_when_push_message_doesnt_exist(): void
    {
        $error1 = new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send');
        $error2 = new PushError(code: PushErrorCode::Unknown, message: 'Unknown error');
        $error3 = new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token');

        $collection = new PushErrorList([$error1, $error2]);

        $this->assertFalse($collection->contains($error3));
    }

    #[Test]
    public function get_returns_correct_push_message(): void
    {
        $error1 = new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send');
        $error2 = new PushError(code: PushErrorCode::Unknown, message: 'Unknown error');

        $collection = new PushErrorList([$error1, $error2]);

        $error = $collection->get(1);

        $this->assertEquals($error2, $error);
    }

    #[Test]
    public function get_returns_null_if_push_message_not_found(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
        ]);

        $error = $collection->get(99);

        $this->assertNull($error);
    }

    #[Test]
    public function count_returns_correct_push_message_count(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
        ]);

        $this->assertCount(3, $collection);
    }

    #[Test]
    public function chunk_returns_correctly_sized_chunks(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
            new PushError(code: PushErrorCode::PushTooManyNotifications, message: 'Too many notifications'),
        ]);

        $chunks = $collection->chunk(2);

        $this->assertCount(2, $chunks);

        foreach ($chunks as $chunk) {
            $this->assertCount(2, $chunk);
        }
    }

    #[Test]
    public function filter_returns_correctly_filtered_collection(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
            new PushError(code: PushErrorCode::PushTooManyNotifications, message: 'Too many notifications'),
        ]);

        $filteredCollection = $collection->filter(fn(PushError $error) => $error->code !== PushErrorCode::Failed);

        $this->assertCount(3, $filteredCollection);
        $this->assertSame(range(0, 2), array_keys($filteredCollection->toArray()));
        $this->assertSame($collection->get(1), $filteredCollection->get(0));
    }

    #[Test]
    public function filter_does_not_affect_original_collection(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
            new PushError(code: PushErrorCode::PushTooManyNotifications, message: 'Too many notifications'),
        ]);

        $collection->filter(fn(PushError $error) => $error->code !== PushErrorCode::Failed);

        $this->assertCount(4, $collection);
    }

    #[Test]
    public function to_array_returns_push_message_array(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
        ]);

        $array = $collection->toArray();

        $this->assertIsArray($array);
        $this->assertCount(3, $array);

        foreach ($array as $error) {
            $this->assertInstanceOf(PushError::class, $error);
        }
    }

    #[Test]
    public function json_encode_returns_valid_json_string(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
        ]);

        $expectedJson = <<<JSON
            [
                {
                    "code": "FAILED",
                    "message": "Push notifications failed to send",
                    "details": null,
                    "endIndex": null,
                    "startIndex": null
                },
                {
                    "code": "UNKNOWN",
                    "message": "Unknown error",
                    "details": null,
                    "endIndex": null,
                    "startIndex": null
                },
                {
                    "code": "UNAUTHORIZED",
                    "message": "Invalid authentication token",
                    "details": null,
                    "endIndex": null,
                    "startIndex": null
                }
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($collection));
    }

    #[Test]
    public function to_json_returns_valid_json_string(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
        ]);

        $expectedJson = <<<JSON
            [
                {
                    "code": "FAILED",
                    "message": "Push notifications failed to send",
                    "details": null,
                    "endIndex": null,
                    "startIndex": null
                },
                {
                    "code": "UNKNOWN",
                    "message": "Unknown error",
                    "details": null,
                    "endIndex": null,
                    "startIndex": null
                },
                {
                    "code": "UNAUTHORIZED",
                    "message": "Invalid authentication token",
                    "details": null,
                    "endIndex": null,
                    "startIndex": null
                }
            ]
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $collection->toJson());
    }

    #[Test]
    public function merge_returns_a_new_list_with_values_from_both_lists(): void
    {
        $collection = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
        ]);

        $merged = $collection->merge(new PushErrorList([
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
        ]));

        $this->assertEquals([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
            new PushError(code: PushErrorCode::Unauthorized, message: 'Invalid authentication token'),
        ], $merged->toArray());

        $this->assertCount(1, $collection);
    }

    #[Test]
    public function can_create_an_iterator_from_a_collection(): void
    {
        $collection = PushErrorList::make([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
        ]);

        $iterator = $collection->getIterator();

        $this->assertInstanceOf(
            Traversable::class,
            $iterator,
        );

        $results = iterator_to_array($iterator);

        $this->assertEquals([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
            new PushError(code: PushErrorCode::Unknown, message: 'Unknown error'),
        ], $results);
    }

    #[Test]
    public function can_check_if_a_collection_is_empty(): void
    {
        $empty = new PushErrorList();

        $notEmpty = new PushErrorList([
            new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'),
        ]);

        $this->assertTrue(
            $empty->isEmpty(),
        );

        $this->assertFalse(
            $notEmpty->isEmpty(),
        );
    }

    #[Test]
    public function constructor_throws_exception_for_invalid_value(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PushErrorList([new PushError(code: PushErrorCode::Failed, message: 'Push notifications failed to send'), new stdClass()]);
    }

    #[Test]
    public function add_throws_exception_for_invalid_value(): void
    {
        $collection = new PushErrorList();

        $this->expectException(InvalidArgumentException::class);

        $collection->add(new stdClass());
    }
}
