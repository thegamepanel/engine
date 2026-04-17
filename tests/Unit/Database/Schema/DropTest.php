<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Schema\Drop;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('drop')]
class DropTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Drop Table
    // -------------------------------------------------------------------------

    #[Test]
    public function dropTable(): void
    {
        $this->assertSame(
            'DROP TABLE `users`',
            Drop::table('users')->toSql(),
        );
    }

    #[Test]
    public function dropTableIfExists(): void
    {
        $this->assertSame(
            'DROP TABLE IF EXISTS `users`',
            Drop::table('users')->ifExists()->toSql(),
        );
    }

    #[Test]
    public function dropTableTemporary(): void
    {
        $this->assertSame(
            'DROP TEMPORARY TABLE `users`',
            Drop::table('users')->temporary()->toSql(),
        );
    }

    #[Test]
    public function dropTableTemporaryIfExists(): void
    {
        $this->assertSame(
            'DROP TEMPORARY TABLE IF EXISTS `users`',
            Drop::table('users')->temporary()->ifExists()->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Drop Column
    // -------------------------------------------------------------------------

    #[Test]
    public function dropColumn(): void
    {
        $this->assertSame(
            'DROP COLUMN `name`',
            Drop::column('name')->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Drop Index
    // -------------------------------------------------------------------------

    #[Test]
    public function dropIndex(): void
    {
        $this->assertSame(
            'DROP INDEX `idx_name`',
            Drop::index('idx_name')->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Drop Primary Key
    // -------------------------------------------------------------------------

    #[Test]
    public function dropPrimaryKey(): void
    {
        $this->assertSame(
            'DROP PRIMARY KEY `pk`',
            Drop::primaryKey('pk')->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Drop Foreign Key
    // -------------------------------------------------------------------------

    #[Test]
    public function dropForeignKey(): void
    {
        $this->assertSame(
            'DROP FOREIGN KEY `fk_name`',
            Drop::foreignKey('fk_name')->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Drop Database
    // -------------------------------------------------------------------------

    #[Test]
    public function dropDatabase(): void
    {
        $this->assertSame(
            'DROP DATABASE `mydb`',
            Drop::database('mydb')->toSql(),
        );
    }

    #[Test]
    public function dropDatabaseIfExists(): void
    {
        $this->assertSame(
            'DROP DATABASE IF EXISTS `mydb`',
            Drop::database('mydb')->ifExists()->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // isDroppingAnIndex
    // -------------------------------------------------------------------------

    #[Test]
    public function isDroppingAnIndexTrueForIndex(): void
    {
        $this->assertTrue(Drop::index('idx')->isDroppingAnIndex());
    }

    #[Test]
    public function isDroppingAnIndexTrueForPrimaryKey(): void
    {
        $this->assertTrue(Drop::primaryKey('pk')->isDroppingAnIndex());
    }

    #[Test]
    public function isDroppingAnIndexTrueForForeignKey(): void
    {
        $this->assertTrue(Drop::foreignKey('fk')->isDroppingAnIndex());
    }

    #[Test]
    public function isDroppingAnIndexFalseForTable(): void
    {
        $this->assertFalse(Drop::table('users')->isDroppingAnIndex());
    }

    #[Test]
    public function isDroppingAnIndexFalseForColumn(): void
    {
        $this->assertFalse(Drop::column('name')->isDroppingAnIndex());
    }

    #[Test]
    public function isDroppingAnIndexFalseForDatabase(): void
    {
        $this->assertFalse(Drop::database('mydb')->isDroppingAnIndex());
    }

    // -------------------------------------------------------------------------
    // isDroppingThePrimaryKey
    // -------------------------------------------------------------------------

    #[Test]
    public function isDroppingThePrimaryKeyTrueForPrimaryKey(): void
    {
        $this->assertTrue(Drop::primaryKey('pk')->isDroppingThePrimaryKey());
    }

    #[Test]
    public function isDroppingThePrimaryKeyFalseForIndex(): void
    {
        $this->assertFalse(Drop::index('idx')->isDroppingThePrimaryKey());
    }

    #[Test]
    public function isDroppingThePrimaryKeyFalseForForeignKey(): void
    {
        $this->assertFalse(Drop::foreignKey('fk')->isDroppingThePrimaryKey());
    }

    #[Test]
    public function isDroppingThePrimaryKeyFalseForTable(): void
    {
        $this->assertFalse(Drop::table('users')->isDroppingThePrimaryKey());
    }

    // -------------------------------------------------------------------------
    // isDroppingAColumn
    // -------------------------------------------------------------------------

    #[Test]
    public function isDroppingAColumnTrueForColumn(): void
    {
        $this->assertTrue(Drop::column('name')->isDroppingAColumn());
    }

    #[Test]
    public function isDroppingAColumnFalseForTable(): void
    {
        $this->assertFalse(Drop::table('users')->isDroppingAColumn());
    }

    #[Test]
    public function isDroppingAColumnFalseForIndex(): void
    {
        $this->assertFalse(Drop::index('idx')->isDroppingAColumn());
    }

    #[Test]
    public function isDroppingAColumnFalseForDatabase(): void
    {
        $this->assertFalse(Drop::database('mydb')->isDroppingAColumn());
    }

    // -------------------------------------------------------------------------
    // getBindings
    // -------------------------------------------------------------------------

    #[Test]
    public function getBindingsReturnsEmptyArrayForTable(): void
    {
        $this->assertSame([], Drop::table('users')->getBindings());
    }

    #[Test]
    public function getBindingsReturnsEmptyArrayForColumn(): void
    {
        $this->assertSame([], Drop::column('name')->getBindings());
    }

    #[Test]
    public function getBindingsReturnsEmptyArrayForIndex(): void
    {
        $this->assertSame([], Drop::index('idx')->getBindings());
    }

    #[Test]
    public function getBindingsReturnsEmptyArrayForPrimaryKey(): void
    {
        $this->assertSame([], Drop::primaryKey('pk')->getBindings());
    }

    #[Test]
    public function getBindingsReturnsEmptyArrayForForeignKey(): void
    {
        $this->assertSame([], Drop::foreignKey('fk')->getBindings());
    }

    #[Test]
    public function getBindingsReturnsEmptyArrayForDatabase(): void
    {
        $this->assertSame([], Drop::database('mydb')->getBindings());
    }
}
