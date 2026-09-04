<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushMessage;

use Dru1x\ExpoPush\PushMessage\InterruptionLevel;
use Dru1x\ExpoPush\PushMessage\Priority;
use Dru1x\ExpoPush\PushMessage\PushMessage;
use Dru1x\ExpoPush\PushMessage\RichContent;
use Dru1x\ExpoPush\PushToken\PushToken;
use Dru1x\ExpoPush\PushToken\PushTokenCollection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
use TypeError;

class PushMessageTest extends TestCase
{
    #[Test]
    public function instantiates_with_single_recipient(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
        );

        $this->assertInstanceOf(PushToken::class, $message->to);
    }

    #[Test]
    public function instantiates_with_multiple_recipients(): void
    {
        $message = new PushMessage(
            to: new PushTokenCollection(
                new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
                new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]'),
                new PushToken('ExponentPushToken[zzzzzzzzzzzzzzzzzzzzzz]'),
            ),
            title: 'Test Notification',
            body: 'This is a test notification',
        );

        $this->assertInstanceOf(PushTokenCollection::class, $message->to);
        $this->assertCount(3, $message->to);
    }

    #[Test]
    public function instantiates_with_data_list(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            data: ['list', 'of', 'values'],
        );

        $this->assertIsList($message->data);
        $this->assertEquals(['list', 'of', 'values'], $message->data);
    }

    #[Test]
    public function instantiates_with_data_map(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            data: ['key' => 'value', 'foo' => 'bar'],
        );

        $this->assertIsArray($message->data);
        $this->assertArrayHasKey('key', $message->data);
        $this->assertEquals('value', $message->data['key']);
        $this->assertArrayHasKey('foo', $message->data);
        $this->assertEquals('bar', $message->data['foo']);
    }

    #[Test]
    public function instantiates_with_data_object(): void
    {
        $dataObject = new stdClass();
        $dataObject->key = 'value';
        $dataObject->foo = 'bar';

        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            data: $dataObject,
        );

        $this->assertIsObject($message->data);
        $this->assertObjectHasProperty('key', $message->data);
        $this->assertEquals('value', $message->data->key);
        $this->assertObjectHasProperty('foo', $message->data);
        $this->assertEquals('bar', $message->data->foo);
    }

    #[Test]
    public function instantiates_with_all_properties(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');
        $expiryTime = time() + 90;
        $richContent = RichContent::fromArray(['image' => 'https://example.com']);

        $message = new PushMessage(
            to: $token,
            title: 'Test Notification',
            subtitle: 'This is a test notification',
            body: 'This is a test notification',
            ttl: 30,
            data: ['list', 'of', 'values'],
            expiration: $expiryTime,
            priority: Priority::Normal,
            sound: 'default',
            badge: 1,
            interruptionLevel: InterruptionLevel::Active,
            channelId: 'test-channel',
            icon: 'test-icon',
            richContent: $richContent,
            categoryId: 'test-category',
            collapseId: 'test-collapse-key',
            tag: 'test-tag',
            mutableContent: true,
            _contentAvailable: false,
        );

        $this->assertSame($token, $message->to);
        $this->assertEquals('Test Notification', $message->title);
        $this->assertEquals('This is a test notification', $message->subtitle);
        $this->assertEquals('This is a test notification', $message->body);
        $this->assertEquals(30, $message->ttl);
        $this->assertEquals(['list', 'of', 'values'], $message->data);
        $this->assertEquals($expiryTime, $message->expiration);
        $this->assertEquals(Priority::Normal, $message->priority);
        $this->assertEquals('default', $message->sound);
        $this->assertEquals(1, $message->badge);
        $this->assertEquals(InterruptionLevel::Active, $message->interruptionLevel);
        $this->assertEquals('test-channel', $message->channelId);
        $this->assertEquals('test-icon', $message->icon);
        $this->assertEquals($richContent, $message->richContent);
        $this->assertEquals('test-category', $message->categoryId);
        $this->assertEquals('test-collapse-key', $message->collapseId);
        $this->assertEquals('test-tag', $message->tag);
        $this->assertTrue($message->mutableContent);
        $this->assertFalse($message->_contentAvailable);
    }

    #[Test]
    public function json_encode_returns_correct_json(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
            data: ['key' => 'value', 'foo' => 'bar'],
            tag: 'test-tag',
        );

        $expectedJson = <<<JSON
            {
                "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
                "title": "Test Notification", 
                "body": "This is a test notification",
                "data": {"key": "value", "foo": "bar"},
                "tag": "test-tag"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($message));
    }

    #[Test]
    public function to_json_returns_correct_json(): void
    {
        $message = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
            data: ['list', 'of', 'values'],
            collapseId: 'test-collapse-key',
        );

        $expectedJson = <<<JSON
            {
                "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
                "title": "Test Notification", 
                "body": "This is a test notification",
                "data": {"0":  "list", "1": "of", "2": "values"},
                "collapseId": "test-collapse-key"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $message->toJson());
    }

    #[Test]
    public function from_array_with_dictionary_returns_instance(): void
    {
        $array = [
            'to' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'title' => 'Test Notification',
            'body' => 'This is a test notification',
            'richContent' => [
                'image' => 'https://example.com',
            ],
            'collapseId' => 'test-collapse-key',
            'tag' => 'test-tag',
        ];

        $message = PushMessage::fromArray($array);

        $this->assertSame('This is a test notification', $message->body);
        $this->assertSame('https://example.com', $message->richContent->image);
        $this->assertSame('test-collapse-key', $message->collapseId);
        $this->assertSame('test-tag', $message->tag);
    }

    #[Test]
    public function from_array_throws_exception_when_to_is_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PushMessage::fromArray([
            'title' => 'Test Notification',
        ]);
    }

    #[Test]
    public function from_array_throws_exception_when_to_is_not_array_or_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PushMessage::fromArray([
            'to' => 123,
        ]);
    }

    #[Test]
    public function from_array_throws_exception_when_rich_content_is_not_array(): void
    {
        $this->expectException(TypeError::class);

        PushMessage::fromArray([
            'to'          => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'richContent' => 'not-an-array',
        ]);
    }

    #[Test]
    public function from_array_converts_priority_to_enum(): void
    {
        $message = PushMessage::fromArray([
            'to'       => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'priority' => 'high',
        ]);

        $this->assertSame(Priority::High, $message->priority);
    }

    #[Test]
    public function from_array_throws_exception_when_priority_is_not_valid(): void
    {
        $this->expectException(TypeError::class);

        PushMessage::fromArray([
            'to'       => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'priority' => [],
        ]);
    }

    #[Test]
    public function from_array_converts_interruption_level_to_enum(): void
    {
        $message = PushMessage::fromArray([
            'to'                => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'interruptionLevel' => 'critical',
        ]);

        $this->assertSame(InterruptionLevel::Critical, $message->interruptionLevel);
    }

    #[Test]
    public function from_array_throws_exception_when_interruption_level_is_not_valid(): void
    {
        $this->expectException(TypeError::class);

        PushMessage::fromArray([
            'to'                => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
            'interruptionLevel' => [],
        ]);
    }

    #[Test]
    public function from_json_returns_instance(): void
    {
        $json = <<<JSON
            {
                "to": "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", 
                "title": "Test Notification", 
                "body": "This is a test notification",
                "richContent": {"image":  "https://example.com"}
            }
            JSON;

        /** @noinspection PhpUnhandledExceptionInspection */
        $message = PushMessage::fromJson($json);

        $this->assertInstanceOf(PushMessage::class, $message);
        $this->assertSame('This is a test notification', $message->body);
        $this->assertSame('https://example.com', $message->richContent->image);
    }

    #[Test]
    public function from_json_with_multiple_tokens_returns_instance(): void
    {
        $json = <<<JSON
            {
                "to": [
                  "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]",
                  "ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]"
                ], 
                "title": "Test Notification", 
                "body": "This is a test notification"
            }
            JSON;

        /** @noinspection PhpUnhandledExceptionInspection */
        $message = PushMessage::fromJson($json);

        $this->assertInstanceOf(PushMessage::class, $message);
        $this->assertInstanceOf(PushTokenCollection::class, $message->to);
    }

    #[Test]
    public function copy_preserves_properties(): void
    {
        $original = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
            collapseId: 'test-collapse-key',
            tag: 'test-tag',
        );

        $copy = $original->copy();

        $this->assertSame('Test Notification', $copy->title);
        $this->assertSame('This is a test notification', $copy->body);
        $this->assertSame('test-collapse-key', $copy->collapseId);
        $this->assertSame('test-tag', $copy->tag);
    }

    #[Test]
    public function copy_replaces_recipient(): void
    {
        $original = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            title: 'Test Notification',
            body: 'This is a test notification',
            collapseId: 'test-collapse-key',
            tag: 'test-tag',
        );

        $newToken = new PushToken('ExponentPushToken[yyyyyyyyyyyyyyyyyyyyyy]');

        $copy = $original->copy(to: $newToken);

        $this->assertSame($newToken, $copy->to);
        $this->assertSame('Test Notification', $copy->title);
        $this->assertSame('This is a test notification', $copy->body);
        $this->assertSame('test-collapse-key', $copy->collapseId);
        $this->assertSame('test-tag', $copy->tag);
    }

    #[Test]
    public function copy_clones_nested_objects(): void
    {
        $original = new PushMessage(
            to: new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]'),
            richContent: RichContent::fromArray(['image' => 'https://example.com']),
        );

        $copy = $original->copy();

        $this->assertNotSame($original->to, $copy->to);
        $this->assertEquals($original->to, $copy->to);

        $this->assertNotSame($original->richContent, $copy->richContent);
        $this->assertEquals($original->richContent, $copy->richContent);
    }

    #[Test]
    public function from_json_with_null_throws_error(): void
    {
        $this->expectException(TypeError::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        PushMessage::fromJson(null);
    }
}
