<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Indexes;

use Engine\Database\Exceptions\InvalidSchemaException;
use Engine\Database\Schema\Indexes\ForeignKey;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('foreign-key')]
class ForeignKeyTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Basic Foreign Key
    // -------------------------------------------------------------------------

    #[Test]
    public function basicForeignKey(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)',
            ForeignKey::make('fk_user', ['user_id'])->on('users')->references('id')->toSql(),
        );
    }

    #[Test]
    public function foreignKeyWithMultipleColumnsAndReferences(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_composite`  FOREIGN KEY (`org_id`, `user_id`) REFERENCES `org_users` (`org_id`, `user_id`)',
            ForeignKey::make('fk_composite', ['org_id', 'user_id'])
                ->on('org_users')
                ->references('org_id', 'user_id')
                ->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Missing Clauses
    // -------------------------------------------------------------------------

    #[Test]
    public function throwsWhenOnIsMissing(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Foreign key is missing a "on" clause');

        ForeignKey::make('fk_user', ['user_id'])->references('id')->toSql();
    }

    #[Test]
    public function throwsWhenOnIsEmpty(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Foreign key is missing a "on" clause');

        ForeignKey::make('fk_user', ['user_id'])->on('')->references('id')->toSql();
    }

    #[Test]
    public function throwsWhenReferencesIsMissing(): void
    {
        $this->expectException(InvalidSchemaException::class);
        $this->expectExceptionMessage('Foreign key is missing a "references" clause');

        ForeignKey::make('fk_user', ['user_id'])->on('users')->toSql();
    }

    // -------------------------------------------------------------------------
    // ON DELETE Actions
    // -------------------------------------------------------------------------

    #[Test]
    public function onDeleteCascade(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onDeleteCascade()
                ->toSql(),
        );
    }

    #[Test]
    public function onDeleteSetNull(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onDeleteSetNull()
                ->toSql(),
        );
    }

    #[Test]
    public function onDeleteRestrict(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onDeleteRestrict()
                ->toSql(),
        );
    }

    #[Test]
    public function onDeleteSetDefault(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET DEFAULT',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onDeleteSetDefault()
                ->toSql(),
        );
    }

    #[Test]
    public function onDeleteNoAction(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onDeleteNoAction()
                ->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // ON UPDATE Actions
    // -------------------------------------------------------------------------

    #[Test]
    public function onUpdateCascade(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onUpdateCascade()
                ->toSql(),
        );
    }

    #[Test]
    public function onUpdateSetNull(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE SET NULL',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onUpdateSetNull()
                ->toSql(),
        );
    }

    #[Test]
    public function onUpdateRestrict(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE RESTRICT',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onUpdateRestrict()
                ->toSql(),
        );
    }

    #[Test]
    public function onUpdateSetDefault(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE SET DEFAULT',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onUpdateSetDefault()
                ->toSql(),
        );
    }

    #[Test]
    public function onUpdateNoAction(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE NO ACTION',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onUpdateNoAction()
                ->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Combined ON DELETE + ON UPDATE
    // -------------------------------------------------------------------------

    #[Test]
    public function combinedOnDeleteAndOnUpdate(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE SET NULL',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onDeleteCascade()
                ->onUpdateSetNull()
                ->toSql(),
        );
    }

    #[Test]
    public function combinedOnDeleteRestrictAndOnUpdateNoAction(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE NO ACTION',
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->onDeleteRestrict()
                ->onUpdateNoAction()
                ->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // getBindings
    // -------------------------------------------------------------------------

    #[Test]
    public function getBindingsReturnsEmptyArray(): void
    {
        $this->assertSame(
            [],
            ForeignKey::make('fk_user', ['user_id'])
                ->on('users')
                ->references('id')
                ->getBindings(),
        );
    }
}
