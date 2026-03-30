<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\WriteResult;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('write-result')]
class WriteResultTest extends TestCase
{
    // -------------------------------------------------------------------------
    // affectedRows()
    // -------------------------------------------------------------------------

    /**
     * - affectedRows() returns the count passed at construction.
     */
    #[Test]
    public function affectedRowsReturnsCount(): void
    {
        $this->assertSame(3, new WriteResult(3, '1')->affectedRows());
    }

    // -------------------------------------------------------------------------
    // lastInsertId()
    // -------------------------------------------------------------------------

    /**
     * - lastInsertId() returns the ID string passed at construction.
     */
    #[Test]
    public function lastInsertIdReturnsIdString(): void
    {
        $this->assertSame('42', new WriteResult(1, '42')->lastInsertId());
    }

    /**
     * - lastInsertId() returns null when no ID was provided.
     */
    #[Test]
    public function lastInsertIdReturnsNullWhenNoId(): void
    {
        $this->assertNull(new WriteResult(1, null)->lastInsertId());
    }

    // -------------------------------------------------------------------------
    // wasSuccessful()
    // -------------------------------------------------------------------------

    /**
     * - wasSuccessful() returns true when at least one row was affected.
     */
    #[Test]
    public function wasSuccessfulReturnsTrueWhenRowsAffected(): void
    {
        $this->assertTrue(new WriteResult(1, null)->wasSuccessful());
    }

    /**
     * - wasSuccessful() returns false when zero rows were affected.
     */
    #[Test]
    public function wasSuccessfulReturnsFalseWhenNoRowsAffected(): void
    {
        $this->assertFalse(new WriteResult(0, null)->wasSuccessful());
    }
}
