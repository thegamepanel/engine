<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Indexes;

use Engine\Database\Schema\Indexes\NamedIndex;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('named-index')]
class NamedIndexTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Unique Index
    // -------------------------------------------------------------------------

    #[Test]
    public function uniqueIndexSingleColumn(): void
    {
        $this->assertSame(
            'UNIQUE INDEX `idx_email` (`email`)',
            NamedIndex::unique('idx_email', ['email'])->toSql(),
        );
    }

    #[Test]
    public function uniqueIndexMultipleColumns(): void
    {
        $this->assertSame(
            'UNIQUE INDEX `idx_org_user` (`org_id`, `user_id`)',
            NamedIndex::unique('idx_org_user', ['org_id', 'user_id'])->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Normal Index
    // -------------------------------------------------------------------------

    #[Test]
    public function normalIndexSingleColumn(): void
    {
        $this->assertSame(
            'INDEX `idx_name` (`name`)',
            NamedIndex::index('idx_name', ['name'])->toSql(),
        );
    }

    #[Test]
    public function normalIndexMultipleColumns(): void
    {
        $this->assertSame(
            'INDEX `idx_name_email` (`name`, `email`)',
            NamedIndex::index('idx_name_email', ['name', 'email'])->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Fulltext Index
    // -------------------------------------------------------------------------

    #[Test]
    public function fulltextIndexSingleColumn(): void
    {
        $this->assertSame(
            'FULLTEXT INDEX `idx_body` (`body`)',
            NamedIndex::fulltext('idx_body', ['body'])->toSql(),
        );
    }

    #[Test]
    public function fulltextIndexMultipleColumns(): void
    {
        $this->assertSame(
            'FULLTEXT INDEX `idx_title_body` (`title`, `body`)',
            NamedIndex::fulltext('idx_title_body', ['title', 'body'])->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // getBindings
    // -------------------------------------------------------------------------

    #[Test]
    public function getBindingsReturnsEmptyArrayForUniqueIndex(): void
    {
        $this->assertSame([], NamedIndex::unique('idx', ['col'])->getBindings());
    }

    #[Test]
    public function getBindingsReturnsEmptyArrayForNormalIndex(): void
    {
        $this->assertSame([], NamedIndex::index('idx', ['col'])->getBindings());
    }

    #[Test]
    public function getBindingsReturnsEmptyArrayForFulltextIndex(): void
    {
        $this->assertSame([], NamedIndex::fulltext('idx', ['col'])->getBindings());
    }
}
