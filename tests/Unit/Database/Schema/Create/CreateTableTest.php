<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Create;

use Engine\Database\Query\Raw;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Create;
use Engine\Database\Schema\Create\CreateTable;
use Engine\Database\Schema\Index;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('create-table')]
class CreateTableTest extends TestCase
{
    #[Test]
    public function basicTableWithSingleColumn(): void
    {
        $table = new CreateTable('users', [
            Column::int('id')->unsigned()->autoIncrement(),
        ]);

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT UNSIGNED AUTO_INCREMENT\n"
            . ')',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithIfNotExists(): void
    {
        $table = new CreateTable('users', [
            Column::int('id'),
        ]);

        $table->ifNotExists();

        $this->assertSame(
            "CREATE  TABLE IF NOT EXISTS `users` (\n"
            . "`id` INT\n"
            . ')',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithTemporary(): void
    {
        $table = new CreateTable('temp_users', [
            Column::int('id'),
        ]);

        $table->temporary();

        $this->assertSame(
            "CREATE TEMPORARY  TABLE `temp_users` (\n"
            . "`id` INT\n"
            . ')',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithEngine(): void
    {
        $table = new CreateTable('users', [
            Column::int('id'),
        ]);

        $table->engine('InnoDB');

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT\n"
            . ') ENGINE InnoDB',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithCharsetAndCollation(): void
    {
        $table = new CreateTable('users', [
            Column::int('id'),
        ]);

        $table->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT\n"
            . ') CHARACTER SET utf8mb4,  COLLATE utf8mb4_unicode_ci',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithAutoIncrementStartingValue(): void
    {
        $table = new CreateTable('users', [
            Column::int('id'),
        ]);

        $table->autoIncrement(1000);

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT\n"
            . ') AUTO_INCREMENT 1000',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithComment(): void
    {
        $table = new CreateTable('users', [
            Column::int('id'),
        ]);

        $table->comment('The users table');

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT\n"
            . ") COMMENT 'The users table'",
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithMultipleColumns(): void
    {
        $table = new CreateTable('users', [
            Column::int('id')->unsigned()->autoIncrement(),
            Column::varchar('name', 255)->notNull(),
            Column::varchar('email', 255)->notNull(),
        ]);

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT UNSIGNED AUTO_INCREMENT,\n"
            . "`name` VARCHAR(255) NOT NULL,\n"
            . "`email` VARCHAR(255) NOT NULL\n"
            . ')',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithColumnsAndIndexes(): void
    {
        $table = new CreateTable('users', [
            Column::int('id')->unsigned()->autoIncrement(),
            Column::varchar('email', 255)->notNull(),
        ]);

        $table->indexes(
            Index::primary('id'),
            Index::unique('idx_email', 'email'),
        );

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT UNSIGNED AUTO_INCREMENT,\n"
            . "`email` VARCHAR(255) NOT NULL,\n"
            . "PRIMARY KEY (`id`),\n"
            . "UNIQUE INDEX `idx_email` (`email`)\n"
            . ')',
            $table->toSql(),
        );
    }

    #[Test]
    public function tableWithAllOptionsCombined(): void
    {
        $table = new CreateTable('users', [
            Column::int('id')->unsigned()->autoIncrement(),
            Column::varchar('name', 255)->notNull(),
        ]);

        $table
            ->temporary()
            ->ifNotExists()
            ->engine('InnoDB')
            ->charset('utf8mb4')
            ->collation('utf8mb4_unicode_ci')
            ->autoIncrement(100)
            ->comment('User accounts')
            ->indexes(
                Index::primary('id'),
            )
        ;

        $this->assertSame(
            "CREATE TEMPORARY  TABLE IF NOT EXISTS `users` (\n"
            . "`id` INT UNSIGNED AUTO_INCREMENT,\n"
            . "`name` VARCHAR(255) NOT NULL,\n"
            . "PRIMARY KEY (`id`)\n"
            . ') ENGINE InnoDB,  CHARACTER SET utf8mb4,  COLLATE utf8mb4_unicode_ci,  AUTO_INCREMENT 100,  COMMENT \'User accounts\'',
            $table->toSql(),
        );
    }

    #[Test]
    public function createTableWithOptions(): void
    {
        $table = new CreateTable('users', [
            Column::int('id'),
        ]);

        $table->options(Raw::from('ROW_FORMAT=DYNAMIC'));

        $this->assertSame(
            "CREATE  TABLE `users` (\n"
            . "`id` INT\n"
            . ')ROW_FORMAT=DYNAMIC',
            $table->toSql(),
        );
    }

    #[Test]
    public function getBindingsReturnsEmptyArray(): void
    {
        $table = new CreateTable('users', [
            Column::int('id'),
        ]);

        $this->assertSame([], $table->getBindings());
    }

    #[Test]
    public function factoryMethodReturnsCreateTableInstance(): void
    {
        $table = Create::table('users', [
            Column::int('id'),
        ]);

        $this->assertInstanceOf(CreateTable::class, $table);
    }
}
