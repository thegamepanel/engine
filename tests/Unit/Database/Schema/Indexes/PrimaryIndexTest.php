<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Indexes;

use Engine\Database\Schema\Indexes\PrimaryIndex;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('primary-index')]
class PrimaryIndexTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Single Column Primary Key
    // -------------------------------------------------------------------------

    #[Test]
    public function singleColumnPrimaryKey(): void
    {
        $this->assertSame(
            'PRIMARY KEY (`id`)',
            PrimaryIndex::make(['id'])->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // Composite Primary Key
    // -------------------------------------------------------------------------

    #[Test]
    public function compositePrimaryKey(): void
    {
        $this->assertSame(
            'PRIMARY KEY (`org_id`, `user_id`)',
            PrimaryIndex::make(['org_id', 'user_id'])->toSql(),
        );
    }

    // -------------------------------------------------------------------------
    // getBindings
    // -------------------------------------------------------------------------

    #[Test]
    public function getBindingsReturnsEmptyArray(): void
    {
        $this->assertSame([], PrimaryIndex::make(['id'])->getBindings());
    }
}
