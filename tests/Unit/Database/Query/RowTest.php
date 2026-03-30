<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Row;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('row')]
class RowTest extends TestCase
{
    // -------------------------------------------------------------------------
    // get()
    // -------------------------------------------------------------------------

    /**
     * - get() returns the value for an existing column.
     */
    #[Test]
    public function getReturnsValueForExistingColumn(): void
    {
        $row = new Row(['name' => 'John', 'age' => 30]);
        $this->assertSame('John', $row->get('name'));
        $this->assertSame(30, $row->get('age'));
    }

    /**
     * - get() returns null for a column that does not exist.
     */
    #[Test]
    public function getReturnsNullForMissingColumn(): void
    {
        $this->assertNull(new Row(['name' => 'John'])->get('missing'));
    }

    // -------------------------------------------------------------------------
    // has()
    // -------------------------------------------------------------------------

    /**
     * - has() returns true when the column exists in the data.
     */
    #[Test]
    public function hasReturnsTrueForExistingColumn(): void
    {
        $this->assertTrue(new Row(['name' => 'John'])->has('name'));
    }

    /**
     * - has() returns false when the column is not present in the data.
     */
    #[Test]
    public function hasReturnsFalseForMissingColumn(): void
    {
        $this->assertFalse(new Row(['name' => 'John'])->has('missing'));
    }

    /**
     * - has() returns true even when the column's value is null.
     */
    #[Test]
    public function hasReturnsTrueForNullValueColumn(): void
    {
        $this->assertTrue(new Row(['deleted_at' => null])->has('deleted_at'));
    }

    // -------------------------------------------------------------------------
    // isNull()
    // -------------------------------------------------------------------------

    /**
     * - isNull() returns true when the column exists and its value is null.
     */
    #[Test]
    public function isNullReturnsTrueForNullValue(): void
    {
        $this->assertTrue(new Row(['deleted_at' => null])->isNull('deleted_at'));
    }

    /**
     * - isNull() returns false when the column exists and has a non-null value.
     */
    #[Test]
    public function isNullReturnsFalseForNonNullValue(): void
    {
        $this->assertFalse(new Row(['name' => 'John'])->isNull('name'));
    }

    /**
     * - isNull() returns false when the column does not exist.
     */
    #[Test]
    public function isNullReturnsFalseForMissingColumn(): void
    {
        $this->assertFalse(new Row([])->isNull('missing'));
    }

    // -------------------------------------------------------------------------
    // toArray()
    // -------------------------------------------------------------------------

    /**
     * - toArray() returns the full underlying data array unchanged.
     */
    #[Test]
    public function toArrayReturnsFullDataArray(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $this->assertSame($data, new Row($data)->toArray());
    }

    // -------------------------------------------------------------------------
    // Typed getters
    // -------------------------------------------------------------------------

    /**
     * - string() returns the column value cast to a string.
     */
    #[Test]
    public function stringReturnsValueAsString(): void
    {
        $this->assertSame('John', new Row(['name' => 'John'])->string('name'));
    }

    /**
     * - int() returns the column value cast to an integer.
     */
    #[Test]
    public function intReturnsValueAsInteger(): void
    {
        $this->assertSame(30, new Row(['age' => 30])->int('age'));
    }

    /**
     * - float() returns the column value cast to a float.
     */
    #[Test]
    public function floatReturnsValueAsFloat(): void
    {
        $this->assertSame(9.99, new Row(['price' => 9.99])->float('price'));
    }

    /**
     * - bool() returns the column value cast to a boolean.
     */
    #[Test]
    public function boolReturnsValueAsBoolean(): void
    {
        $this->assertTrue(new Row(['active' => true])->bool('active'));
    }

    /**
     * - array() returns the column value cast to an array.
     */
    #[Test]
    public function arrayReturnsValueAsArray(): void
    {
        $this->assertSame([1, 2], new Row(['tags' => [1, 2]])->array('tags'));
    }
}
