<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Schema\Index;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('schema'), Group('index')]
class IndexTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Primary key
    // -------------------------------------------------------------------------

    /**
     * - Primary key with a single column produces correct SQL.
     */
    #[Test]
    public function primaryKeySingleColumnProducesCorrectSql(): void
    {
        $index = Index::primary('id');

        $this->assertSame('PRIMARY KEY (`id`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    /**
     * - Primary key with composite columns produces correct SQL.
     */
    #[Test]
    public function primaryKeyCompositeColumnsProducesCorrectSql(): void
    {
        $index = Index::primary(['tenant_id', 'id']);

        $this->assertSame('PRIMARY KEY (`tenant_id`, `id`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    // -------------------------------------------------------------------------
    // Unique index
    // -------------------------------------------------------------------------

    /**
     * - Unique index with a single column produces correct SQL.
     */
    #[Test]
    public function uniqueIndexSingleColumnProducesCorrectSql(): void
    {
        $index = Index::unique('users_email_unique', 'email');

        $this->assertSame('UNIQUE INDEX `users_email_unique` (`email`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    /**
     * - Unique index with composite columns produces correct SQL.
     */
    #[Test]
    public function uniqueIndexCompositeColumnsProducesCorrectSql(): void
    {
        $index = Index::unique('users_tenant_email_unique', ['tenant_id', 'email']);

        $this->assertSame('UNIQUE INDEX `users_tenant_email_unique` (`tenant_id`, `email`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    // -------------------------------------------------------------------------
    // Regular index
    // -------------------------------------------------------------------------

    /**
     * - Regular index with a single column produces correct SQL.
     */
    #[Test]
    public function indexSingleColumnProducesCorrectSql(): void
    {
        $index = Index::index('users_name_index', 'name');

        $this->assertSame('INDEX `users_name_index` (`name`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    /**
     * - Regular index with composite columns produces correct SQL.
     */
    #[Test]
    public function indexCompositeColumnsProducesCorrectSql(): void
    {
        $index = Index::index('users_name_email_index', ['name', 'email']);

        $this->assertSame('INDEX `users_name_email_index` (`name`, `email`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    // -------------------------------------------------------------------------
    // Fulltext index
    // -------------------------------------------------------------------------

    /**
     * - Fulltext index with a single column produces correct SQL.
     */
    #[Test]
    public function fulltextIndexSingleColumnProducesCorrectSql(): void
    {
        $index = Index::fulltext('posts_body_fulltext', 'body');

        $this->assertSame('FULLTEXT INDEX `posts_body_fulltext` (`body`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    /**
     * - Fulltext index with composite columns produces correct SQL.
     */
    #[Test]
    public function fulltextIndexCompositeColumnsProducesCorrectSql(): void
    {
        $index = Index::fulltext('posts_title_body_fulltext', ['title', 'body']);

        $this->assertSame('FULLTEXT INDEX `posts_title_body_fulltext` (`title`, `body`)', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }

    // -------------------------------------------------------------------------
    // Foreign key
    // -------------------------------------------------------------------------

    /**
     * - Foreign key with references produces correct SQL.
     */
    #[Test]
    public function foreignKeyWithReferencesProducesCorrectSql(): void
    {
        $index = Index::foreign('posts_user_id_foreign', 'user_id')
            ->references('users', 'id')
        ;

        $this->assertSame(
            'CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)',
            $index->toSql(),
        );
        $this->assertSame([], $index->getBindings());
    }

    /**
     * - Foreign key with onDelete produces correct SQL.
     */
    #[Test]
    public function foreignKeyWithOnDeleteProducesCorrectSql(): void
    {
        $index = Index::foreign('posts_user_id_foreign', 'user_id')
            ->references('users', 'id')
            ->onDelete('cascade')
        ;

        $this->assertSame(
            'CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
            $index->toSql(),
        );
    }

    /**
     * - Foreign key with onUpdate produces correct SQL.
     */
    #[Test]
    public function foreignKeyWithOnUpdateProducesCorrectSql(): void
    {
        $index = Index::foreign('posts_user_id_foreign', 'user_id')
            ->references('users', 'id')
            ->onUpdate('set null')
        ;

        $this->assertSame(
            'CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE SET NULL',
            $index->toSql(),
        );
    }

    /**
     * - Foreign key with both onDelete and onUpdate produces correct SQL.
     */
    #[Test]
    public function foreignKeyWithBothActionsProducesCorrectSql(): void
    {
        $index = Index::foreign('posts_user_id_foreign', 'user_id')
            ->references('users', 'id')
            ->onDelete('cascade')
            ->onUpdate('set null')
        ;

        $this->assertSame(
            'CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)'
            . ' ON DELETE CASCADE ON UPDATE SET NULL',
            $index->toSql(),
        );
    }

    /**
     * - Foreign key with composite columns produces correct SQL.
     */
    #[Test]
    public function foreignKeyCompositeColumnsProducesCorrectSql(): void
    {
        $index = Index::foreign('orders_tenant_user_foreign', ['tenant_id', 'user_id'])
            ->references('users', ['tenant_id', 'id'])
            ->onDelete('cascade')
        ;

        $this->assertSame(
            'CONSTRAINT `orders_tenant_user_foreign` FOREIGN KEY (`tenant_id`, `user_id`)'
            . ' REFERENCES `users` (`tenant_id`, `id`) ON DELETE CASCADE',
            $index->toSql(),
        );
    }

    /**
     * - Foreign key without references produces constraint with just the key columns.
     */
    #[Test]
    public function foreignKeyWithoutReferencesProducesConstraintOnly(): void
    {
        $index = Index::foreign('posts_user_id_foreign', 'user_id');

        $this->assertSame(
            'CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`)',
            $index->toSql(),
        );
    }

    /**
     * - Foreign key actions are uppercased regardless of input case.
     */
    #[Test]
    public function foreignKeyActionsAreUppercased(): void
    {
        $index = Index::foreign('fk_test', 'col')
            ->references('other', 'id')
            ->onDelete('Cascade')
            ->onUpdate('Set Null')
        ;

        $this->assertSame(
            'CONSTRAINT `fk_test` FOREIGN KEY (`col`) REFERENCES `other` (`id`)'
            . ' ON DELETE CASCADE ON UPDATE SET NULL',
            $index->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Named reference
    // -------------------------------------------------------------------------

    /**
     * - named() produces just the backtick-quoted index name.
     */
    #[Test]
    public function namedProducesQuotedName(): void
    {
        $index = Index::named('users_email_unique');

        $this->assertSame('`users_email_unique`', $index->toSql());
        $this->assertSame([], $index->getBindings());
    }
}
