<?php

namespace Dru1x\ExpoPush\Tests\Unit\Support;

use ArrayIterator;
use Dru1x\ExpoPush\Support\Map as MapInterface;
use Dru1x\ExpoPush\Support\MapMethods;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

class MapTest extends TestCase
{
    #[Test]
    #[DataProvider('constructProvider')]
    public function can_make_a_map(iterable $data): void
    {
        $this->assertMap(
            ['foo' => 1, 'bar' => 2],
            Map::make($data),
        );
    }

    #[Test]
    public function constructing_a_map_preserves_keys(): void
    {
        $this->assertMap(
            [3 => 'foo', 'a' => 'bar', 1 => 'baz'],
            new Map([3 => 'foo', 'a' => 'bar', 1 => 'baz']),
        );
    }

    #[Test]
    public function can_create_a_map_from_an_array(): void
    {
        $this->assertMap(
            ['foo' => 1],
            Map::fromArray(['foo' => 1]),
        );
    }

    #[Test]
    public function can_count_a_map(): void
    {
        $this->assertCount(
            2,
            Map::make(['foo' => 1, 'bar' => 2]),
        );
    }

    #[Test]
    #[DataProvider('getProvider')]
    public function can_retrieve_a_map_value_by_its_key(int|string $key, mixed $value): void
    {
        $this->assertSame(
            $value,
            Map::make(['foo' => 1, 'bar' => 2, 5 => 3])->get($key),
        );
    }

    #[Test]
    public function can_put_a_value_into_a_map(): void
    {
        $map = Map::make(['foo' => 1]);
        $map->put('bar', 2);
        $map->put(7, 3);

        $this->assertMap(['foo' => 1, 'bar' => 2, 7 => 3], $map);
    }

    #[Test]
    public function putting_a_value_at_an_existing_key_replaces_it(): void
    {
        $map = Map::make(['foo' => 1, 'bar' => 2]);
        $map->put('foo', 3);

        $this->assertMap(['foo' => 3, 'bar' => 2], $map);
    }

    #[Test]
    #[DataProvider('putAllProvider')]
    public function can_put_multiple_values_into_a_map(array|MapInterface $values): void
    {
        $map = Map::make(['foo' => 1]);
        $map->putAll($values);

        $this->assertMap(['foo' => 1, 'bar' => 2, 9 => 3], $map);
    }

    #[Test]
    public function can_break_a_map_into_chunks_preserving_keys(): void
    {
        $chunks = Map::make(['foo' => 1, 'bar' => 2, 9 => 3])->chunk(2);

        $this->assertCount(2, $chunks);
        $this->assertContainsOnlyInstancesOf(Map::class, $chunks);
        $this->assertMap(['foo' => 1, 'bar' => 2], $chunks[0]);
        $this->assertMap([9 => 3], $chunks[1]);
    }

    #[Test]
    #[DataProvider('filterProvider')]
    public function can_filter_a_map_preserving_keys(array $items, callable $callable, array $expected): void
    {
        $this->assertMap(
            $expected,
            Map::make($items)->filter($callable),
        );
    }

    #[Test]
    public function filtering_does_not_affect_the_original_map(): void
    {
        $map = Map::make(['foo' => 1, 'bar' => 2]);
        $map->filter(fn() => false);

        $this->assertMap(['foo' => 1, 'bar' => 2], $map);
    }

    #[Test]
    #[DataProvider('mergeProvider')]
    public function can_merge_two_maps(array $first, array $second, array $expected): void
    {
        $map    = Map::make($first);
        $merged = $map->merge(Map::make($second));

        $this->assertInstanceOf(Map::class, $merged);
        $this->assertMap($expected, $merged);
        $this->assertMap($first, $map);
    }

    #[Test]
    public function can_reduce_a_map_to_a_single_value(): void
    {
        $this->assertSame(
            6,
            Map::make(['foo' => 1, 'bar' => 2, 'baz' => 3])->reduce(fn(int $carry, int $item) => $carry + $item, 0),
        );
    }

    #[Test]
    public function can_map_over_a_map_preserving_keys(): void
    {
        $result = Map::make(['foo' => 1, 'bar' => 2, 9 => 3])->map(fn(int $item, int|string $key) => "$key:$item");

        $this->assertSame(
            ['foo' => 'foo:1', 'bar' => 'bar:2', 9 => '9:3'],
            $result,
        );
    }

    #[Test]
    public function can_check_if_a_map_contains_a_given_value(): void
    {
        $map = Map::make(['foo' => 1, 'bar' => 2]);

        $this->assertTrue($map->contains(2));
        $this->assertFalse($map->contains('2'));
        $this->assertFalse($map->doesNotContain(2));
        $this->assertTrue($map->doesNotContain(3));
    }

    #[Test]
    public function can_create_an_iterator_from_a_map(): void
    {
        $iterator = Map::make(['foo' => 1, 9 => 2])->getIterator();

        $this->assertInstanceOf(Traversable::class, $iterator);
        $this->assertSame(['foo' => 1, 9 => 2], iterator_to_array($iterator));
    }

    #[Test]
    public function can_convert_a_map_to_json(): void
    {
        $map = Map::make(['foo' => 1, 'bar' => 'two']);

        $this->assertSame('{"foo":1,"bar":"two"}', $map->toJson());
        $this->assertSame('{"foo":1,"bar":"two"}', json_encode($map));
    }

    #[Test]
    public function can_create_a_map_from_json(): void
    {
        $this->assertMap(
            ['foo' => 1, 'bar' => 'two'],
            Map::fromJson('{"foo":1,"bar":"two"}'),
        );
    }

    #[Test]
    public function can_check_if_a_map_is_empty(): void
    {
        $empty    = Map::make();
        $notEmpty = Map::make(['foo' => 0]);

        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($empty->isNotEmpty());

        $this->assertFalse($notEmpty->isEmpty());
        $this->assertTrue($notEmpty->isNotEmpty());
    }

    #[Test]
    public function putting_an_invalid_key_throws_an_exception(): void
    {
        $map = Map::make();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid key of type string');

        $map->put('invalid', 1);
    }

    #[Test]
    public function putting_an_invalid_value_throws_an_exception(): void
    {
        $map = Map::make();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value of type stdClass');

        $map->put('foo', new stdClass());
    }

    #[Test]
    public function constructing_with_an_invalid_value_throws_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Map(['foo' => 1, 'bar' => new stdClass()]);
    }

    protected function assertMap(array $expected, Map $actual): void
    {
        self::assertThat(
            $actual->toArray(),
            new IsIdentical($expected),
        );
    }

    public static function constructProvider(): array
    {
        return [
            'array' => [['foo' => 1, 'bar' => 2]],
            'iterable' => [new ArrayIterator(['foo' => 1, 'bar' => 2])],
            'generator' => [(function () {
                yield 'foo' => 1;
                yield 'bar' => 2;
            })()],
        ];
    }

    public static function getProvider(): array
    {
        return [
            'string key present' => ['bar', 2],
            'string key not present' => ['baz', null],
            'integer key present' => [5, 3],
            'integer key not present' => [0, null],
        ];
    }

    public static function putAllProvider(): array
    {
        return [
            'array' => [['bar' => 2, 9 => 3]],
            'map' => [Map::make(['bar' => 2, 9 => 3])],
        ];
    }

    public static function filterProvider(): array
    {
        return [
            'passing callable' => [['a' => 1, 'b' => 2, 'c' => 3], fn(int $number) => $number % 2, ['a' => 1, 'c' => 3]],
            'using key' => [['a' => 1, 'b' => 2, 'c' => 3], fn(int $_, string $key) => $key === 'b', ['b' => 2]],
            'integer keys' => [[3 => 1, 7 => 2, 9 => 3], fn(int $number) => $number > 1, [7 => 2, 9 => 3]],
        ];
    }

    public static function mergeProvider(): array
    {
        return [
            'string keys' => [['foo' => 1, 'bar' => 2], ['bar' => 3, 'baz' => 4], ['foo' => 1, 'bar' => 3, 'baz' => 4]],
            'integer keys' => [[0 => 1, 5 => 2], [5 => 3, 1000 => 4], [0 => 1, 5 => 3, 1000 => 4]],
            'mixed keys' => [['foo' => 1, 2 => 2], [2 => 3], ['foo' => 1, 2 => 3]],
        ];
    }
}

// Helper Classes ----

class Map implements MapInterface
{
    use MapMethods;

    protected function keyIsValid(mixed $key): bool
    {
        return $key !== 'invalid';
    }

    protected function valueIsValid(mixed $value): bool
    {
        return !$value instanceof stdClass;
    }
}
