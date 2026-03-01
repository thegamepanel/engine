<?php
declare(strict_types=1);

namespace Tests\Unit\Values\Concerns;

use Engine\Values\Concerns\GetValueAsType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

final class GetValueAsTypeTest extends TestCase
{
    /**
     * Create a test harness that uses the trait with the given values.
     *
     * @param array<string, mixed> $data
     */
    private function harness(array $data): object
    {
        return new class ($data) {
            use GetValueAsType;

            /** @param array<string, mixed> $data */
            public function __construct(private readonly array $data) {}

            protected function getValue(string $name): mixed
            {
                return $this->data[$name] ?? null;
            }
        };
    }

    // ── string() ──────────────────────────────────────────────

    #[Test]
    public function string_returns_string_directly(): void
    {
        $this->assertSame('hello', $this->harness(['v' => 'hello'])->string('v'));
    }

    #[Test]
    public function string_converts_integer(): void
    {
        $this->assertSame('42', $this->harness(['v' => 42])->string('v'));
    }

    #[Test]
    public function string_converts_float(): void
    {
        $this->assertSame('3.14', $this->harness(['v' => 3.14])->string('v'));
    }

    #[Test]
    public function string_returns_numeric_string_as_is(): void
    {
        $this->assertSame('42', $this->harness(['v' => '42'])->string('v'));
    }

    #[Test]
    public function string_throws_on_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => null])->string('v');
    }

    #[Test]
    public function string_throws_on_bool(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => true])->string('v');
    }

    #[Test]
    public function string_throws_on_array(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => [1, 2]])->string('v');
    }

    // ── int() ─────────────────────────────────────────────────

    #[Test]
    public function int_returns_int_directly(): void
    {
        $this->assertSame(42, $this->harness(['v' => 42])->int('v'));
    }

    #[Test]
    public function int_converts_numeric_string(): void
    {
        $this->assertSame(42, $this->harness(['v' => '42'])->int('v'));
    }

    #[Test]
    public function int_converts_float(): void
    {
        $this->assertSame(3, $this->harness(['v' => 3.0])->int('v'));
    }

    #[Test]
    public function int_throws_on_non_numeric_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => 'abc'])->int('v');
    }

    #[Test]
    public function int_throws_on_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => null])->int('v');
    }

    #[Test]
    public function int_throws_on_bool(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => true])->int('v');
    }

    // ── float() ───────────────────────────────────────────────

    #[Test]
    public function float_returns_float_directly(): void
    {
        $this->assertSame(3.14, $this->harness(['v' => 3.14])->float('v'));
    }

    #[Test]
    public function float_converts_numeric_string(): void
    {
        $this->assertSame(3.14, $this->harness(['v' => '3.14'])->float('v'));
    }

    #[Test]
    public function float_converts_int(): void
    {
        $this->assertSame(42.0, $this->harness(['v' => 42])->float('v'));
    }

    #[Test]
    public function float_throws_on_non_numeric_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => 'abc'])->float('v');
    }

    #[Test]
    public function float_throws_on_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => null])->float('v');
    }

    // ── bool() ────────────────────────────────────────────────

    #[Test]
    public function bool_returns_true_directly(): void
    {
        $this->assertTrue($this->harness(['v' => true])->bool('v'));
    }

    #[Test]
    public function bool_returns_false_directly(): void
    {
        $this->assertFalse($this->harness(['v' => false])->bool('v'));
    }

    #[Test]
    public function bool_converts_int_one_to_true(): void
    {
        $this->assertTrue($this->harness(['v' => 1])->bool('v'));
    }

    #[Test]
    public function bool_converts_int_zero_to_false(): void
    {
        $this->assertFalse($this->harness(['v' => 0])->bool('v'));
    }

    #[Test]
    public function bool_converts_non_zero_int_to_true(): void
    {
        $this->assertTrue($this->harness(['v' => 42])->bool('v'));
    }

    #[Test]
    public function bool_converts_string_true(): void
    {
        $this->assertTrue($this->harness(['v' => 'true'])->bool('v'));
    }

    #[Test]
    public function bool_converts_string_one(): void
    {
        $this->assertTrue($this->harness(['v' => '1'])->bool('v'));
    }

    #[Test]
    public function bool_converts_string_yes(): void
    {
        $this->assertTrue($this->harness(['v' => 'yes'])->bool('v'));
    }

    #[Test]
    public function bool_converts_string_false(): void
    {
        $this->assertFalse($this->harness(['v' => 'false'])->bool('v'));
    }

    #[Test]
    public function bool_converts_string_zero(): void
    {
        $this->assertFalse($this->harness(['v' => '0'])->bool('v'));
    }

    #[Test]
    public function bool_converts_string_no(): void
    {
        $this->assertFalse($this->harness(['v' => 'no'])->bool('v'));
    }

    #[Test]
    public function bool_throws_on_invalid_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => 'maybe'])->bool('v');
    }

    #[Test]
    public function bool_throws_on_float(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => 1.5])->bool('v');
    }

    #[Test]
    public function bool_throws_on_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => null])->bool('v');
    }

    // ── array() ───────────────────────────────────────────────

    #[Test]
    public function array_returns_array_directly(): void
    {
        $this->assertSame([1, 2, 3], $this->harness(['v' => [1, 2, 3]])->array('v'));
    }

    #[Test]
    public function array_decodes_json_object_string(): void
    {
        $this->assertSame(['a' => 1], $this->harness(['v' => '{"a":1}'])->array('v'));
    }

    #[Test]
    public function array_decodes_json_array_string(): void
    {
        $this->assertSame([1, 2, 3], $this->harness(['v' => '[1,2,3]'])->array('v'));
    }

    #[Test]
    public function array_decodes_empty_json_object(): void
    {
        $this->assertSame([], $this->harness(['v' => '{}'])->array('v'));
    }

    #[Test]
    public function array_decodes_empty_json_array(): void
    {
        $this->assertSame([], $this->harness(['v' => '[]'])->array('v'));
    }

    #[Test]
    public function array_throws_on_invalid_json(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => '{invalid'])->array('v');
    }

    #[Test]
    public function array_throws_on_int(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => 42])->array('v');
    }

    #[Test]
    public function array_throws_on_null(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->harness(['v' => null])->array('v');
    }

    #[Test]
    public function array_with_json_scalar_causes_type_error(): void
    {
        // Valid JSON scalar passes json_validate() but json_decode() returns
        // a non-array, causing a TypeError from the strict return type.
        $this->expectException(TypeError::class);

        $this->harness(['v' => '"hello"'])->array('v');
    }
}
