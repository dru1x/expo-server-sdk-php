<?php

namespace Dru1x\ExpoPush\Tests\Unit\PushMessage;

use Dru1x\ExpoPush\PushMessage\RichContent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RichContentTest extends TestCase
{
    #[Test]
    public function json_encode_returns_value(): void
    {
        $richContent = new RichContent('https://file.cloud/abc-123');

        $expectedJson = <<<JSON
            {
              "image": "https://file.cloud/abc-123"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($richContent));
    }

    #[Test]
    public function to_json_returns_value(): void
    {
        $richContent = new RichContent('https://file.cloud/abc-123');

        $expectedJson = <<<JSON
            {
              "image": "https://file.cloud/abc-123"
            }
            JSON;

        $this->assertJsonStringEqualsJsonString($expectedJson, $richContent->toJson());
    }
}
