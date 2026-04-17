<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Schema\Index;
use Engine\Database\Schema\Indexes\ForeignKey;
use Engine\Database\Schema\Indexes\NamedIndex;
use Engine\Database\Schema\Indexes\PrimaryIndex;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('index-factory')]
class IndexFactoryTest extends TestCase
{
    // -------------------------------------------------------------------------
    // foreign()
    // -------------------------------------------------------------------------

    #[Test]
    public function foreignReturnsForeignKey(): void
    {
        $this->assertInstanceOf(ForeignKey::class, Index::foreign('fk_user', 'user_id'));
    }

    #[Test]
    public function foreignDelegatesToForeignKeyMake(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)',
            Index::foreign('fk_user', 'user_id')->on('users')->references('id')->toSql(),
        );
    }

    #[Test]
    public function foreignWithMultipleColumns(): void
    {
        $this->assertSame(
            'CONSTRAINT `fk_composite`  FOREIGN KEY (`org_id`, `user_id`) REFERENCES `teams` (`org_id`, `user_id`)',
            Index::foreign('fk_composite', 'org_id', 'user_id')
                ->on('teams')
                ->references('org_id', 'user_id')
                ->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // unique()
    // -------------------------------------------------------------------------

    #[Test]
    public function uniqueReturnsNamedIndex(): void
    {
        $this->assertInstanceOf(NamedIndex::class, Index::unique('idx_email', 'email'));
    }

    #[Test]
    public function uniqueDelegatesToNamedIndexUnique(): void
    {
        $this->assertSame(
            'UNIQUE INDEX `idx_email` (`email`)',
            Index::unique('idx_email', 'email')->toSql(),
        );
    }

    #[Test]
    public function uniqueWithMultipleColumns(): void
    {
        $this->assertSame(
            'UNIQUE INDEX `idx_org_user` (`org_id`, `user_id`)',
            Index::unique('idx_org_user', 'org_id', 'user_id')->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // index()
    // -------------------------------------------------------------------------

    #[Test]
    public function indexReturnsNamedIndex(): void
    {
        $this->assertInstanceOf(NamedIndex::class, Index::index('idx_name', 'name'));
    }

    #[Test]
    public function indexDelegatesToNamedIndexIndex(): void
    {
        $this->assertSame(
            'INDEX `idx_name` (`name`)',
            Index::index('idx_name', 'name')->toSql(),
        );
    }

    #[Test]
    public function indexWithMultipleColumns(): void
    {
        $this->assertSame(
            'INDEX `idx_name_email` (`name`, `email`)',
            Index::index('idx_name_email', 'name', 'email')->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // fulltext()
    // -------------------------------------------------------------------------

    #[Test]
    public function fulltextReturnsNamedIndex(): void
    {
        $this->assertInstanceOf(NamedIndex::class, Index::fulltext('idx_body', 'body'));
    }

    #[Test]
    public function fulltextDelegatesToNamedIndexFulltext(): void
    {
        $this->assertSame(
            'FULLTEXT INDEX `idx_body` (`body`)',
            Index::fulltext('idx_body', 'body')->toSql(),
        );
    }

    #[Test]
    public function fulltextWithMultipleColumns(): void
    {
        $this->assertSame(
            'FULLTEXT INDEX `idx_title_body` (`title`, `body`)',
            Index::fulltext('idx_title_body', 'title', 'body')->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // primary()
    // -------------------------------------------------------------------------

    #[Test]
    public function primaryReturnsPrimaryIndex(): void
    {
        $this->assertInstanceOf(PrimaryIndex::class, Index::primary('id'));
    }

    #[Test]
    public function primaryDelegatesToPrimaryIndexMake(): void
    {
        $this->assertSame(
            'PRIMARY KEY (`id`)',
            Index::primary('id')->toSql(),
        );
    }

    #[Test]
    public function primaryWithMultipleColumns(): void
    {
        $this->assertSame(
            'PRIMARY KEY (`org_id`, `user_id`)',
            Index::primary('org_id', 'user_id')->toSql(),
        );
    }
}
