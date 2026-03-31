<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Contracts\Schema;
use Engine\Database\Schema\Blueprint;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Index;
use Engine\Database\Schema\Table;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('schema'), Group('table')]
class TableTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Contract
    // -------------------------------------------------------------------------

    /**
     * - Table implements Schema interface.
     */
    #[Test]
    public function implementsSchemaInterface(): void
    {
        $table = Table::drop('users');

        $this->assertInstanceOf(Schema::class, $table);
    }

    // -------------------------------------------------------------------------
    // Create mode
    // -------------------------------------------------------------------------

    /**
     * - create() with columns and indexes produces correct CREATE TABLE SQL.
     */
    #[Test]
    public function createProducesCorrectSql(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
            Column::string('name', 255),
            Index::primary('id'),
        ]);

        $expected = "CREATE TABLE `users` (\n"
            . "    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
            . "    `name` VARCHAR(255) NOT NULL,\n"
            . "    PRIMARY KEY (`id`)\n"
            . ')';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    /**
     * - create() with ifNotExists() produces IF NOT EXISTS.
     */
    #[Test]
    public function createWithIfNotExistsProducesCorrectSql(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
        ])->ifNotExists();

        $expected = "CREATE TABLE IF NOT EXISTS `users` (\n"
            . "    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT\n"
            . ')';

        $this->assertSame($expected, $table->toSql());
    }

    /**
     * - create() with engine() produces ENGINE clause.
     */
    #[Test]
    public function createWithEngineProducesCorrectSql(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id'),
        ])->engine('InnoDB');

        $expected = "CREATE TABLE `users` (\n"
            . "    `id` BIGINT NOT NULL\n"
            . ') ENGINE = InnoDB';

        $this->assertSame($expected, $table->toSql());
    }

    /**
     * - create() with charset() produces DEFAULT CHARACTER SET clause.
     */
    #[Test]
    public function createWithCharsetProducesCorrectSql(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id'),
        ])->charset('utf8mb4');

        $expected = "CREATE TABLE `users` (\n"
            . "    `id` BIGINT NOT NULL\n"
            . ') DEFAULT CHARACTER SET utf8mb4';

        $this->assertSame($expected, $table->toSql());
    }

    /**
     * - create() with collation() produces DEFAULT COLLATE clause.
     */
    #[Test]
    public function createWithCollationProducesCorrectSql(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id'),
        ])->collation('utf8mb4_unicode_ci');

        $expected = "CREATE TABLE `users` (\n"
            . "    `id` BIGINT NOT NULL\n"
            . ') DEFAULT COLLATE utf8mb4_unicode_ci';

        $this->assertSame($expected, $table->toSql());
    }

    /**
     * - create() with all options combined produces correct SQL.
     */
    #[Test]
    public function createWithAllOptionsProducesCorrectSql(): void
    {
        $table = Table::create('users', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
            Column::string('name', 255),
            Column::string('email', 255),
            Index::primary('id'),
            Index::unique('users_email_unique', 'email'),
        ])
            ->ifNotExists()
            ->engine('InnoDB')
            ->charset('utf8mb4')
            ->collation('utf8mb4_unicode_ci')
        ;

        $expected = "CREATE TABLE IF NOT EXISTS `users` (\n"
            . "    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,\n"
            . "    `name` VARCHAR(255) NOT NULL,\n"
            . "    `email` VARCHAR(255) NOT NULL,\n"
            . "    PRIMARY KEY (`id`),\n"
            . "    UNIQUE INDEX `users_email_unique` (`email`)\n"
            . ')'
            . ' ENGINE = InnoDB'
            . ' DEFAULT CHARACTER SET utf8mb4'
            . ' DEFAULT COLLATE utf8mb4_unicode_ci';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    // -------------------------------------------------------------------------
    // Drop mode
    // -------------------------------------------------------------------------

    /**
     * - drop() produces DROP TABLE SQL.
     */
    #[Test]
    public function dropProducesCorrectSql(): void
    {
        $table = Table::drop('users');

        $this->assertSame('DROP TABLE `users`', $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    /**
     * - drop() with ifExists() produces IF EXISTS.
     */
    #[Test]
    public function dropWithIfExistsProducesCorrectSql(): void
    {
        $table = Table::drop('users')->ifExists();

        $this->assertSame('DROP TABLE IF EXISTS `users`', $table->toSql());
    }

    // -------------------------------------------------------------------------
    // Alter closure mode
    // -------------------------------------------------------------------------

    /**
     * - alter() with multiple operations produces correct ALTER TABLE SQL.
     */
    #[Test]
    public function alterWithMultipleOperationsProducesCorrectSql(): void
    {
        $table = Table::alter('users', function (Blueprint $blueprint) {
            $blueprint->add(Column::string('bio', 500)->nullable());
            $blueprint->modify(Column::string('email', 500));
            $blueprint->drop(Column::named('avatar'));
            $blueprint->drop(Index::named('users_avatar_index'));
            $blueprint->rename(Column::named('name'), 'full_name');
        });

        $expected = 'ALTER TABLE `users` '
            . 'ADD COLUMN `bio` VARCHAR(500) NULL, '
            . 'MODIFY COLUMN `email` VARCHAR(500) NOT NULL, '
            . 'DROP COLUMN `avatar`, '
            . 'DROP INDEX `users_avatar_index`, '
            . 'RENAME COLUMN `name` TO `full_name`';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    /**
     * - alter() with a single foreign key add operation.
     */
    #[Test]
    public function alterWithSingleForeignKeyAddProducesCorrectSql(): void
    {
        $table = Table::alter('posts', function (Blueprint $blueprint) {
            $blueprint->add(
                Index::foreign('posts_user_id_fk', 'user_id')
                    ->references('users', 'id')
                    ->onDelete('cascade'),
            );
        });

        $expected = 'ALTER TABLE `posts` ADD '
            . 'CONSTRAINT `posts_user_id_fk` FOREIGN KEY (`user_id`)'
            . ' REFERENCES `users` (`id`)'
            . ' ON DELETE CASCADE';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    // -------------------------------------------------------------------------
    // Shortcuts
    // -------------------------------------------------------------------------

    /**
     * - addColumns() produces ALTER TABLE with ADD COLUMN clauses.
     */
    #[Test]
    public function addColumnsProducesCorrectSql(): void
    {
        $table = Table::addColumns('users', [
            Column::string('bio', 500)->nullable(),
            Column::string('avatar', 255)->nullable(),
        ]);

        $expected = 'ALTER TABLE `users` '
            . 'ADD COLUMN `bio` VARCHAR(500) NULL, '
            . 'ADD COLUMN `avatar` VARCHAR(255) NULL';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    /**
     * - dropColumns() with string array produces ALTER TABLE with DROP COLUMN clauses.
     */
    #[Test]
    public function dropColumnsProducesCorrectSql(): void
    {
        $table = Table::dropColumns('users', ['bio', 'avatar']);

        $expected = 'ALTER TABLE `users` '
            . 'DROP COLUMN `bio`, '
            . 'DROP COLUMN `avatar`';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    /**
     * - addIndexes() produces ALTER TABLE with ADD index clauses.
     */
    #[Test]
    public function addIndexesProducesCorrectSql(): void
    {
        $table = Table::addIndexes('users', [
            Index::unique('users_email_unique', 'email'),
            Index::index('users_name_index', 'name'),
        ]);

        $expected = 'ALTER TABLE `users` '
            . 'ADD UNIQUE INDEX `users_email_unique` (`email`), '
            . 'ADD INDEX `users_name_index` (`name`)';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    /**
     * - dropIndexes() with string array produces ALTER TABLE with DROP INDEX clauses.
     */
    #[Test]
    public function dropIndexesProducesCorrectSql(): void
    {
        $table = Table::dropIndexes('users', ['users_email_unique', 'users_name_index']);

        $expected = 'ALTER TABLE `users` '
            . 'DROP INDEX `users_email_unique`, '
            . 'DROP INDEX `users_name_index`';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }

    /**
     * - renameColumn() produces ALTER TABLE with RENAME COLUMN clause.
     */
    #[Test]
    public function renameColumnProducesCorrectSql(): void
    {
        $table = Table::renameColumn('users', 'name', 'full_name');

        $expected = 'ALTER TABLE `users` RENAME COLUMN `name` TO `full_name`';

        $this->assertSame($expected, $table->toSql());
        $this->assertSame([], $table->getBindings());
    }
}
