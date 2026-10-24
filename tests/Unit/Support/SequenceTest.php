<?php

namespace Dru1x\ExpoPush\Tests\Unit\Support;

use ArrayIterator;
use Dru1x\ExpoPush\Support\Sequence as SequenceInterface;
use Dru1x\ExpoPush\Support\SequenceMethods;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\TestCase;
use stdClass;
use Traversable;

class SequenceTest extends TestCase
{
    #[Test]
    #[DataProvider('constructProvider')]
    public function can_make_a_sequence(iterable $data): void
    {
        $this->assertSequence(
            [1, 2],
            Sequence::make($data),
        );
    }

    #[Test]
    public function constructing_a_sequence_discards_keys(): void
    {
        $this->assertSequence(
            ['foo', 'bar', 'baz'],
            new Sequence([3 => 'foo', 'a' => 'bar', 1 => 'baz']),
        );
    }

    #[Test]
    public function can_create_a_sequence_from_an_array(): void
    {
        $this->assertSequence(
            [1, 2],
            Sequence::fromArray([1, 2]),
        );
    }

    #[Test]
    public function can_count_a_sequence(): void
    {
        $this->assertCount(
            2,
            Sequence::make([1, 2]),
        );
    }

    #[Test]
    #[DataProvider('containsProvider')]
    public function can_check_if_a_sequence_contains_a_given_value(mixed $item, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Sequence::make([1, 2])->contains($item),
        );
    }

    #[Test]
    #[DataProvider('doesNotContainProvider')]
    public function can_check_if_a_sequence_does_not_contain_a_given_value(mixed $item, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Sequence::make([1, 2])->doesNotContain($item),
        );
    }

    #[Test]
    #[DataProvider('getProvider')]
    public function can_retrieve_a_sequence_value_by_its_key(int $key, mixed $value): void
    {
        $this->assertSame(
            $value,
            Sequence::make([1, 2, 3])->get($key),
        );
    }

    #[Test]
    public function can_get_the_first_value_of_a_sequence(): void
    {
        $this->assertSame(1, Sequence::make([1, 2, 3])->first());
        $this->assertNull(Sequence::make()->first());
    }

    #[Test]
    public function can_get_the_last_value_of_a_sequence(): void
    {
        $this->assertSame(3, Sequence::make([1, 2, 3])->last());
        $this->assertNull(Sequence::make()->last());
    }

    #[Test]
    public function can_push_a_value_to_a_sequence(): void
    {
        $sequence = Sequence::make([1, 2]);
        $sequence->push(3);

        $this->assertSequence([1, 2, 3], $sequence);
    }

    #[Test]
    public function can_push_multiple_values_to_a_sequence(): void
    {
        $sequence = Sequence::make([1, 2]);
        $sequence->push(3, 4);

        $this->assertSequence([1, 2, 3, 4], $sequence);
    }

    #[Test]
    public function pushing_a_spread_sequence_appends_its_values(): void
    {
        $sequence = Sequence::make([1, 2]);
        $sequence->push(...Sequence::make([3, 4]));

        $this->assertSequence([1, 2, 3, 4], $sequence);
    }

    #[Test]
    public function pushing_no_values_leaves_the_sequence_unchanged(): void
    {
        $sequence = Sequence::make([1, 2]);
        $sequence->push();

        $this->assertSequence([1, 2], $sequence);
    }

    #[Test]
    public function can_break_a_sequence_into_chunks(): void
    {
        $chunks = Sequence::make([1, 2, 3])->chunk(2);

        $this->assertCount(2, $chunks);
        $this->assertContainsOnlyInstancesOf(Sequence::class, $chunks);
        $this->assertSequence([1, 2], $chunks[0]);
        $this->assertSequence([3], $chunks[1]);
    }

    #[Test]
    #[DataProvider('filterProvider')]
    public function can_filter_a_sequence(array $items, callable $callable, array $expected): void
    {
        $this->assertSequence(
            $expected,
            Sequence::make($items)->filter($callable),
        );
    }

    #[Test]
    public function filtering_does_not_affect_the_original_sequence(): void
    {
        $sequence = Sequence::make([1, 2, 3]);
        $sequence->filter(fn() => false);

        $this->assertSequence([1, 2, 3], $sequence);
    }

    #[Test]
    public function can_merge_two_sequences(): void
    {
        $sequence = Sequence::make([1, 2]);
        $merged   = $sequence->merge(Sequence::make([3, 4]));

        $this->assertInstanceOf(Sequence::class, $merged);
        $this->assertSequence([1, 2, 3, 4], $merged);
        $this->assertSequence([1, 2], $sequence);
    }

    #[Test]
    #[DataProvider('reduceProvider')]
    public function can_reduce_a_sequence_to_a_single_value(array $items, callable $callable, mixed $initial, mixed $expected): void
    {
        $this->assertSame(
            $expected,
            Sequence::make($items)->reduce($callable, $initial),
        );
    }

    #[Test]
    #[DataProvider('toArrayProvider')]
    public function can_convert_a_nested_sequence_to_an_array(array $items): void
    {
        $this->assertSame(
            [...$items],
            Sequence::make($items)->toArray(),
        );
    }

    #[Test]
    public function can_map_over_a_sequence(): void
    {
        $result = Sequence::make([1, 2, 3])->map(fn(int $item) => $item * $item);

        $this->assertSame(
            [1, 4, 9],
            $result,
        );
    }

    #[Test]
    public function can_map_over_a_sequence_using_keys(): void
    {
        $result = Sequence::make(['a', 'b'])->map(fn(string $item, int $key) => "$key:$item");

        $this->assertSame(
            ['0:a', '1:b'],
            $result,
        );
    }

    #[Test]
    #[DataProvider('constructProvider')]
    public function can_create_an_iterator_from_a_sequence(iterable $items): void
    {
        $iterator = Sequence::make($items)->getIterator();

        $this->assertInstanceOf(Traversable::class, $iterator);
        $this->assertSame([1, 2], iterator_to_array($iterator));
    }

    #[Test]
    public function can_convert_a_sequence_to_json(): void
    {
        $sequence = Sequence::make([1, 'two', ['three' => 3]]);

        $this->assertSame('[1,"two",{"three":3}]', $sequence->toJson());
        $this->assertSame('[1,"two",{"three":3}]', json_encode($sequence));
    }

    #[Test]
    public function can_create_a_sequence_from_json(): void
    {
        $this->assertSequence(
            [1, 'two'],
            Sequence::fromJson('[1,"two"]'),
        );
    }

    #[Test]
    public function can_check_if_a_sequence_is_empty(): void
    {
        $empty    = Sequence::make();
        $notEmpty = Sequence::make([0]);

        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($empty->isNotEmpty());

        $this->assertFalse($notEmpty->isEmpty());
        $this->assertTrue($notEmpty->isNotEmpty());
    }

    #[Test]
    public function pushing_an_invalid_value_throws_an_exception(): void
    {
        $sequence = Sequence::make();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value of type stdClass');

        $sequence->push(new stdClass());
    }

    #[Test]
    public function pushing_an_invalid_value_among_valid_values_throws_an_exception(): void
    {
        $sequence = Sequence::make();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value of type stdClass');

        $sequence->push(3, new stdClass());
    }

    #[Test]
    public function constructing_with_an_invalid_value_throws_an_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Sequence([1, new stdClass()]);
    }

    protected function assertSequence(array $expected, Sequence $actual): void
    {
        self::assertThat(
            $actual->toArray(),
            new IsIdentical($expected),
        );
    }

    public static function constructProvider(): array
    {
        return [
            'array' => [[1, 2]],
            'iterable' => [new ArrayIterator([1, 2])],
            'generator' => [(function () {
                foreach ([1, 2] as $item) {
                    yield $item;
                }
            })()],
        ];
    }

    public static function containsProvider(): array
    {
        return [
            'does' => [2, true],
            'doesnt' => [3, false],
            'doesnt loose' => ['2', false],
        ];
    }

    public static function doesNotContainProvider(): array
    {
        return [
            'does' => [2, false],
            'doesnt' => [3, true],
            'doesnt loose' => ['2', true],
        ];
    }

    public static function getProvider(): array
    {
        return [
            'key present' => [2, 3],
            'key not present' => [5, null],
        ];
    }

    public static function filterProvider(): array
    {
        return [
            'passing callable' => [[1, 2, 3, 4, 5], fn(int $number) => $number % 2, [1, 3, 5]],
            'using key' => [[1, 2, 3, 4, 5], fn(int $_, int $key) => $key % 2, [2, 4]],
        ];
    }

    public static function reduceProvider(): array
    {
        return [
            'integers' => [[1, 2, 3], fn(?int $carry, int $item) => $carry + $item, null, 6],
            'integers with initial' => [[1, 2, 3], fn(int $carry, int $item) => $carry + $item, 4, 10],
            'strings' => [['one', 'two', 'three'], fn(string $carry, string $item) => $carry . $item, '', 'onetwothree'],
            'strings with initial' => [['one', 'two', 'three'], fn(string $carry, string $item) => $carry . $item, 'zero', 'zeroonetwothree'],
        ];
    }

    public static function toArrayProvider(): array
    {
        return [
            'array' => [[1, 2, 3]],
            'nested array' => [[1, [2], [[3]]]],
            'nested sequence' => [[1, Sequence::make([2]), Sequence::make([Sequence::make([3])])]],
        ];
    }
}

// Helper Classes ----

class Sequence implements SequenceInterface
{
    use SequenceMethods;

    protected function valueIsValid(mixed $value): bool
    {
        return !$value instanceof stdClass;
    }
}
