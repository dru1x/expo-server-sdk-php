<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushToken;

use Dru1x\ExpoPush\PushToken\PushToken;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

class PushTokenTest extends TestCase
{
    #[Test]
    public function valid_exponent_prefix_format_value_is_accepted(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertSame('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $token->value);
    }

    #[Test]
    public function valid_expo_prefix_format_value_is_accepted(): void
    {
        $token = new PushToken('ExpoPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertSame('ExpoPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $token->value);
    }

    #[Test]
    public function valid_legacy_uuid_value_is_accepted(): void
    {
        $token = new PushToken('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa');

        $this->assertSame('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', $token->value);
    }

    #[Test]
    public function valid_non_alpha_numerical_body_is_accepted(): void
    {
        $token = new PushToken('ExpoPushToken[xxxxxxxxxxxxxxxxxxxxx!]');

        $this->assertSame('ExpoPushToken[xxxxxxxxxxxxxxxxxxxxx!]', $token->value);
    }

    #[Test]
    public function valid_empty_body_is_accepted(): void
    {
        $token = new PushToken('ExpoPushToken[]');

        $this->assertSame('ExpoPushToken[]', $token->value);
    }

    #[Test]
    public function invalid_value_throws_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PushToken('NOT A TOKEN');
    }

    #[Test]
    public function casts_to_string(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', (string) $token);
    }

    #[Test]
    public function to_string_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $this->assertEquals('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $token->toString());
    }

    #[Test]
    public function json_encode_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $expectedJson = <<<JSON
            "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($token));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $token = new PushToken('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]');

        $expectedJson = <<<JSON
            "ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $token->toJson());
    }

    #[Test]
    public function from_array_with_dictionary_returns_instance(): void
    {
        $array = [
            'value' => 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]',
        ];

        $token = PushToken::fromArray($array);

        $this->assertInstanceOf(PushToken::class, $token);
        $this->assertSame('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $token->value);
    }

    #[Test]
    public function from_json_returns_instance(): void
    {
        $json = '"ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]"';

        $token = PushToken::fromJson($json);

        $this->assertInstanceOf(PushToken::class, $token);
        $this->assertSame('ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]', $token->value);
    }

    #[Test]
    public function from_json_with_null_throws_error(): void
    {
        $this->expectException(TypeError::class);

        PushToken::fromJson(null);
    }
}
