<?php
declare(strict_types=1);

namespace Tests\Unit\Values;

use Engine\Values\Exceptions\InvalidValueCastException;
use Engine\Values\ValueGetter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('values'), Group('value-getter')]
class ValueGetterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // string()
    // -------------------------------------------------------------------------

    /**
     * - string() returns the value unchanged when it is already a string.
     */
    #[Test]
    public function stringReturnsStringValue(): void
    {
        $this->assertSame('hello', ValueGetter::string('k', ['k' => 'hello']));
    }

    /**
     * - string() casts a numeric int value to a string.
     */
    #[Test]
    public function stringCastsNumericToString(): void
    {
        $this->assertSame('42', ValueGetter::string('k', ['k' => 42]));
    }

    /**
     * - string() throws InvalidValueCastException for a non-castable value (bool).
     */
    #[Test]
    public function stringThrowsForNonCastableValue(): void
    {
        $this->expectException(InvalidValueCastException::class);
        ValueGetter::string('k', ['k' => true]);
    }

    // -------------------------------------------------------------------------
    // float()
    // -------------------------------------------------------------------------

    /**
     * - float() returns the value unchanged when it is already a float.
     */
    #[Test]
    public function floatReturnsFloatValue(): void
    {
        $this->assertSame(3.14, ValueGetter::float('k', ['k' => 3.14]));
    }

    /**
     * - float() casts a numeric string to a float.
     */
    #[Test]
    public function floatCastsNumericStringToFloat(): void
    {
        $this->assertSame(3.14, ValueGetter::float('k', ['k' => '3.14']));
    }

    /**
     * - float() throws InvalidValueCastException for a non-numeric string.
     */
    #[Test]
    public function floatThrowsForNonCastableValue(): void
    {
        $this->expectException(InvalidValueCastException::class);
        ValueGetter::float('k', ['k' => 'not a number']);
    }

    // -------------------------------------------------------------------------
    // bool()
    // -------------------------------------------------------------------------

    /**
     * - bool() returns the value unchanged when it is already a boolean.
     */
    #[Test]
    public function boolReturnsBooleanValue(): void
    {
        $this->assertTrue(ValueGetter::bool('k', ['k' => true]));
        $this->assertFalse(ValueGetter::bool('k', ['k' => false]));
    }

    /**
     * - bool() casts an int to a boolean (1 => true, 0 => false).
     */
    #[Test]
    public function boolCastsIntToBoolean(): void
    {
        $this->assertTrue(ValueGetter::bool('k', ['k' => 1]));
        $this->assertFalse(ValueGetter::bool('k', ['k' => 0]));
    }

    /**
     * - bool() converts recognised truthy/falsy strings to a boolean.
     */
    #[Test]
    public function boolCastsStringToBoolean(): void
    {
        $this->assertTrue(ValueGetter::bool('k', ['k' => 'true']));
        $this->assertTrue(ValueGetter::bool('k', ['k' => '1']));
        $this->assertTrue(ValueGetter::bool('k', ['k' => 'yes']));
        $this->assertFalse(ValueGetter::bool('k', ['k' => 'false']));
        $this->assertFalse(ValueGetter::bool('k', ['k' => '0']));
        $this->assertFalse(ValueGetter::bool('k', ['k' => 'no']));
    }

    /**
     * - bool() throws InvalidValueCastException for an unrecognised string value.
     */
    #[Test]
    public function boolThrowsForUnrecognisedString(): void
    {
        $this->expectException(InvalidValueCastException::class);
        ValueGetter::bool('k', ['k' => 'maybe']);
    }

    /**
     * - bool() throws InvalidValueCastException for a non-castable type (float).
     */
    #[Test]
    public function boolThrowsForNonCastableValue(): void
    {
        $this->expectException(InvalidValueCastException::class);
        ValueGetter::bool('k', ['k' => 3.14]);
    }

    // -------------------------------------------------------------------------
    // array()
    // -------------------------------------------------------------------------

    /**
     * - array() returns the value unchanged when it is already an array.
     */
    #[Test]
    public function arrayReturnsArrayValue(): void
    {
        $this->assertSame([1, 2], ValueGetter::array('k', ['k' => [1, 2]]));
    }

    /**
     * - array() decodes a valid JSON string into an array.
     */
    #[Test]
    public function arrayDecodesJsonString(): void
    {
        $this->assertSame([1, 2], ValueGetter::array('k', ['k' => '[1,2]']));
    }

    /**
     * - array() throws InvalidValueCastException for a non-castable type (int).
     */
    #[Test]
    public function arrayThrowsForNonCastableValue(): void
    {
        $this->expectException(InvalidValueCastException::class);
        ValueGetter::array('k', ['k' => 42]);
    }

    // -------------------------------------------------------------------------
    // int()
    // -------------------------------------------------------------------------

    /**
     * - int() returns the value unchanged when it is already an integer.
     */
    #[Test]
    public function intReturnsIntegerValue(): void
    {
        $this->assertSame(42, ValueGetter::int('k', ['k' => 42]));
    }

    /**
     * - int() casts a numeric string to an integer.
     */
    #[Test]
    public function intCastsNumericStringToInteger(): void
    {
        $this->assertSame(42, ValueGetter::int('k', ['k' => '42']));
    }

    /**
     * - int() throws InvalidValueCastException for a non-numeric string.
     */
    #[Test]
    public function intThrowsForNonCastableValue(): void
    {
        $this->expectException(InvalidValueCastException::class);
        ValueGetter::int('k', ['k' => 'not a number']);
    }
}
