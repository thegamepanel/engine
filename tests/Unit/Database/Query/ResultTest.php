<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Result;
use Engine\Database\Query\Row;
use PDOStatement;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('result')]
class ResultTest extends TestCase
{
    // -------------------------------------------------------------------------
    // first()
    // -------------------------------------------------------------------------

    /**
     * - first() returns the first row as a Row instance with correct data.
     */
    #[Test]
    public function firstReturnsFirstRow(): void
    {
        $result = $this->createResult([['name' => 'Alice'], ['name' => 'Bob']]);
        $this->assertInstanceOf(Row::class, $result->first());
        $this->assertSame('Alice', $result->first()->get('name'));
    }

    /**
     * - first() returns null when the result set is empty.
     */
    #[Test]
    public function firstReturnsNullWhenEmpty(): void
    {
        $this->assertNull($this->createResult([])->first());
    }

    // -------------------------------------------------------------------------
    // all()
    // -------------------------------------------------------------------------

    /**
     * - all() returns every row as a Row object with correct data.
     */
    #[Test]
    public function allReturnsAllRowsAsRowObjects(): void
    {
        $result = $this->createResult([['name' => 'Alice'], ['name' => 'Bob']]);
        $rows   = $result->all();
        $this->assertCount(2, $rows);
        $this->assertInstanceOf(Row::class, $rows[0]);
        $this->assertSame('Bob', $rows[1]->get('name'));
    }

    // -------------------------------------------------------------------------
    // each()
    // -------------------------------------------------------------------------

    /**
     * - each() invokes the callback for every row in order.
     */
    #[Test]
    public function eachIteratesOverAllRows(): void
    {
        $result = $this->createResult([['name' => 'Alice'], ['name' => 'Bob']]);
        $names  = [];
        $result->each(function (Row $row) use (&$names) {
            $names[] = $row->get('name');
        });
        $this->assertSame(['Alice', 'Bob'], $names);
    }

    // -------------------------------------------------------------------------
    // count()
    // -------------------------------------------------------------------------

    /**
     * - count() returns the number of rows reported by the statement.
     */
    #[Test]
    public function countReturnsRowCount(): void
    {
        $this->assertSame(2, $this->createResult([['a' => 1], ['a' => 2]])->count());
    }

    // -------------------------------------------------------------------------
    // isEmpty()
    // -------------------------------------------------------------------------

    /**
     * - isEmpty() returns true when the result set contains no rows.
     */
    #[Test]
    public function isEmptyReturnsTrueWhenNoRows(): void
    {
        $this->assertTrue($this->createResult([])->isEmpty());
    }

    /**
     * - isEmpty() returns false when the result set contains at least one row.
     */
    #[Test]
    public function isEmptyReturnsFalseWhenRowsExist(): void
    {
        $this->assertFalse($this->createResult([['a' => 1]])->isEmpty());
    }

    private function createResult(array $rows): Result
    {
        $statement = $this->createStub(PDOStatement::class);
        $statement->method('fetchAll')->willReturn($rows);
        $statement->method('rowCount')->willReturn(count($rows));
        return new Result($statement, []);
    }
}
