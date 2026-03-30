<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query;

use Engine\Database\Query\Expressions\RawExpression;
use Engine\Database\Query\Insert;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('insert')]
class InsertTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Single row insert
    // -------------------------------------------------------------------------

    /**
     * - A single-row insert produces correct SQL with placeholders.
     */
    #[Test]
    public function singleRowInsertProducesCorrectSql(): void
    {
        $query = Insert::into('users')->values(['name' => 'John', 'age' => 30]);

        $this->assertSame('INSERT INTO users (name, age) VALUES (?, ?)', $query->toSql());
        $this->assertSame(['John', 30], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // Bulk insert
    // -------------------------------------------------------------------------

    /**
     * - Multiple values() calls produce bulk insert SQL with multiple value tuples.
     */
    #[Test]
    public function bulkInsertProducesCorrectSql(): void
    {
        $query = Insert::into('users')
            ->values(['name' => 'John', 'age' => 30])
            ->values(['name' => 'Jane', 'age' => 25])
        ;

        $this->assertSame('INSERT INTO users (name, age) VALUES (?, ?), (?, ?)', $query->toSql());
        $this->assertSame(['John', 30, 'Jane', 25], $query->getBindings());
    }

    /**
     * - Three rows produce three value tuples with all bindings in order.
     */
    #[Test]
    public function threeRowsProduceThreeValueTuples(): void
    {
        $query = Insert::into('logs')
            ->values(['level' => 'info', 'message' => 'a'])
            ->values(['level' => 'warn', 'message' => 'b'])
            ->values(['level' => 'error', 'message' => 'c'])
        ;

        $this->assertSame(
            'INSERT INTO logs (level, message) VALUES (?, ?), (?, ?), (?, ?)',
            $query->toSql(),
        );
        $this->assertSame(['info', 'a', 'warn', 'b', 'error', 'c'], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // ignore()
    // -------------------------------------------------------------------------

    /**
     * - ignore() produces INSERT IGNORE INTO SQL.
     */
    #[Test]
    public function ignoreProducesInsertIgnoreSql(): void
    {
        $query = Insert::into('users')
            ->ignore()
            ->values(['name' => 'John'])
        ;

        $this->assertSame('INSERT IGNORE INTO users (name) VALUES (?)', $query->toSql());
        $this->assertSame(['John'], $query->getBindings());
    }

    /**
     * - ignore() can be called after values().
     */
    #[Test]
    public function ignoreCanBeCalledAfterValues(): void
    {
        $query = Insert::into('users')
            ->values(['name' => 'John'])
            ->ignore()
        ;

        $this->assertSame('INSERT IGNORE INTO users (name) VALUES (?)', $query->toSql());
    }

    // -------------------------------------------------------------------------
    // replace()
    // -------------------------------------------------------------------------

    /**
     * - replace() produces REPLACE INTO SQL.
     */
    #[Test]
    public function replaceProducesReplaceIntoSql(): void
    {
        $query = Insert::into('users')
            ->replace()
            ->values(['name' => 'John'])
        ;

        $this->assertSame('REPLACE INTO users (name) VALUES (?)', $query->toSql());
        $this->assertSame(['John'], $query->getBindings());
    }

    /**
     * - replace() can be called after values().
     */
    #[Test]
    public function replaceCanBeCalledAfterValues(): void
    {
        $query = Insert::into('users')
            ->values(['name' => 'John'])
            ->replace()
        ;

        $this->assertSame('REPLACE INTO users (name) VALUES (?)', $query->toSql());
    }

    // -------------------------------------------------------------------------
    // upsert()
    // -------------------------------------------------------------------------

    /**
     * - upsert() with plain values appends ON DUPLICATE KEY UPDATE clause.
     */
    #[Test]
    public function upsertWithPlainValuesProducesCorrectSql(): void
    {
        $query = Insert::into('users')
            ->values(['name' => 'John', 'email' => 'john@example.com'])
            ->upsert(['email' => 'john@new.com'])
        ;

        $this->assertSame(
            'INSERT INTO users (name, email) VALUES (?, ?) ON DUPLICATE KEY UPDATE email = ?',
            $query->toSql(),
        );
        $this->assertSame(['John', 'john@example.com', 'john@new.com'], $query->getBindings());
    }

    /**
     * - upsert() with Expression values renders SQL inline.
     */
    #[Test]
    public function upsertWithExpressionRendersInlineSql(): void
    {
        $query = Insert::into('counters')
            ->values(['name' => 'visits', 'count' => 1])
            ->upsert(['count' => RawExpression::make('count + ?', [1])])
        ;

        $this->assertSame(
            'INSERT INTO counters (name, count) VALUES (?, ?) ON DUPLICATE KEY UPDATE count = count + ?',
            $query->toSql(),
        );
        $this->assertSame(['visits', 1, 1], $query->getBindings());
    }

    /**
     * - upsert() with multiple columns produces correct SQL.
     */
    #[Test]
    public function upsertWithMultipleColumnsProducesCorrectSql(): void
    {
        $query = Insert::into('users')
            ->values(['name' => 'John', 'email' => 'john@example.com', 'age' => 30])
            ->upsert(['email' => 'john@new.com', 'age' => 31])
        ;

        $this->assertSame(
            'INSERT INTO users (name, email, age) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE email = ?, age = ?',
            $query->toSql(),
        );
        $this->assertSame(['John', 'john@example.com', 30, 'john@new.com', 31], $query->getBindings());
    }

    // -------------------------------------------------------------------------
    // Combinations
    // -------------------------------------------------------------------------

    /**
     * - ignore() combined with upsert() produces correct SQL.
     */
    #[Test]
    public function ignoreCombinedWithUpsertProducesCorrectSql(): void
    {
        $query = Insert::into('users')
            ->ignore()
            ->values(['name' => 'John', 'email' => 'john@example.com'])
            ->upsert(['email' => 'john@new.com'])
        ;

        $this->assertSame(
            'INSERT IGNORE INTO users (name, email) VALUES (?, ?) ON DUPLICATE KEY UPDATE email = ?',
            $query->toSql(),
        );
        $this->assertSame(['John', 'john@example.com', 'john@new.com'], $query->getBindings());
    }

    /**
     * - Bulk insert combined with upsert() produces correct SQL and bindings.
     */
    #[Test]
    public function bulkInsertCombinedWithUpsertProducesCorrectSql(): void
    {
        $query = Insert::into('users')
            ->values(['name' => 'John', 'email' => 'john@example.com'])
            ->values(['name' => 'Jane', 'email' => 'jane@example.com'])
            ->upsert(['email' => RawExpression::make('VALUES(email)', [])])
        ;

        $this->assertSame(
            'INSERT INTO users (name, email) VALUES (?, ?), (?, ?) ON DUPLICATE KEY UPDATE email = VALUES(email)',
            $query->toSql(),
        );
        $this->assertSame(['John', 'john@example.com', 'Jane', 'jane@example.com'], $query->getBindings());
    }
}
