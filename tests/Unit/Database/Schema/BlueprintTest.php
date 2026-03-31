<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Schema\Blueprint;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Index;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('schema'), Group('blueprint')]
class BlueprintTest extends TestCase
{
    // -------------------------------------------------------------------------
    // add()
    // -------------------------------------------------------------------------

    /**
     * - add() with a Column produces ADD COLUMN clause.
     */
    #[Test]
    public function addColumnProducesCorrectSql(): void
    {
        $blueprint = new Blueprint();
        $blueprint->add(Column::string('bio', 500)->nullable());

        $this->assertSame('ADD COLUMN `bio` VARCHAR(500) NULL', $blueprint->toSql());
        $this->assertSame([], $blueprint->getBindings());
    }

    /**
     * - add() with an Index produces ADD clause with index SQL.
     */
    #[Test]
    public function addIndexProducesCorrectSql(): void
    {
        $blueprint = new Blueprint();
        $blueprint->add(Index::unique('idx_email', 'email'));

        $this->assertSame('ADD UNIQUE INDEX `idx_email` (`email`)', $blueprint->toSql());
        $this->assertSame([], $blueprint->getBindings());
    }

    /**
     * - add() with a foreign key Index produces ADD CONSTRAINT clause.
     */
    #[Test]
    public function addForeignKeyProducesCorrectSql(): void
    {
        $blueprint = new Blueprint();
        $blueprint->add(
            Index::foreign('fk_user_id', 'user_id')
                ->references('users', 'id')
                ->onDelete('CASCADE'),
        );

        $this->assertSame(
            'ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
            $blueprint->toSql(),
        );
        $this->assertSame([], $blueprint->getBindings());
    }

    // -------------------------------------------------------------------------
    // modify()
    // -------------------------------------------------------------------------

    /**
     * - modify() produces MODIFY COLUMN clause.
     */
    #[Test]
    public function modifyProducesCorrectSql(): void
    {
        $blueprint = new Blueprint();
        $blueprint->modify(Column::string('email', 500));

        $this->assertSame('MODIFY COLUMN `email` VARCHAR(500) NOT NULL', $blueprint->toSql());
        $this->assertSame([], $blueprint->getBindings());
    }

    // -------------------------------------------------------------------------
    // drop()
    // -------------------------------------------------------------------------

    /**
     * - drop() with a Column produces DROP COLUMN clause.
     */
    #[Test]
    public function dropColumnProducesCorrectSql(): void
    {
        $blueprint = new Blueprint();
        $blueprint->drop(Column::named('avatar'));

        $this->assertSame('DROP COLUMN `avatar`', $blueprint->toSql());
        $this->assertSame([], $blueprint->getBindings());
    }

    /**
     * - drop() with an Index produces DROP INDEX clause.
     */
    #[Test]
    public function dropIndexProducesCorrectSql(): void
    {
        $blueprint = new Blueprint();
        $blueprint->drop(Index::named('idx_email'));

        $this->assertSame('DROP INDEX `idx_email`', $blueprint->toSql());
        $this->assertSame([], $blueprint->getBindings());
    }

    // -------------------------------------------------------------------------
    // rename()
    // -------------------------------------------------------------------------

    /**
     * - rename() produces RENAME COLUMN clause.
     */
    #[Test]
    public function renameProducesCorrectSql(): void
    {
        $blueprint = new Blueprint();
        $blueprint->rename(Column::named('name'), 'display_name');

        $this->assertSame('RENAME COLUMN `name` TO `display_name`', $blueprint->toSql());
        $this->assertSame([], $blueprint->getBindings());
    }

    // -------------------------------------------------------------------------
    // Multiple operations
    // -------------------------------------------------------------------------

    /**
     * - Multiple operations are joined with commas.
     */
    #[Test]
    public function multipleOperationsJoinedWithCommas(): void
    {
        $blueprint = new Blueprint();
        $blueprint
            ->add(Column::string('bio', 500)->nullable())
            ->modify(Column::string('email', 500))
            ->drop(Column::named('avatar'))
            ->rename(Column::named('name'), 'display_name')
        ;

        $this->assertSame(
            'ADD COLUMN `bio` VARCHAR(500) NULL'
            . ', MODIFY COLUMN `email` VARCHAR(500) NOT NULL'
            . ', DROP COLUMN `avatar`'
            . ', RENAME COLUMN `name` TO `display_name`',
            $blueprint->toSql(),
        );
        $this->assertSame([], $blueprint->getBindings());
    }
}
