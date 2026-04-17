<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('string-column')]
class StringColumnTest extends TestCase
{
    #[Test]
    public function charColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`name` CHAR', Column::char('name')->toSql());
    }

    #[Test]
    public function charColumnWithLength(): void
    {
        $this->assertSame('`name` CHAR(10)', Column::char('name')->length(10)->toSql());
    }

    #[Test]
    public function varcharColumnWithLength(): void
    {
        $this->assertSame('`name` VARCHAR(255)', Column::varchar('name')->length(255)->toSql());
    }

    #[Test]
    public function textColumnWithLength(): void
    {
        $this->assertSame('`body` TEXT(500)', Column::text('body')->length(500)->toSql());
    }

    #[Test]
    public function tinytextColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`excerpt` TINYTEXT', Column::tinytext('excerpt')->toSql());
    }

    #[Test]
    public function mediumtextColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`content` MEDIUMTEXT', Column::mediumtext('content')->toSql());
    }

    #[Test]
    public function longtextColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`body` LONGTEXT', Column::longtext('body')->toSql());
    }

    #[Test]
    public function textColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`body` TEXT', Column::text('body')->toSql());
    }

    #[Test]
    public function varcharColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`name` VARCHAR', Column::varchar('name')->toSql());
    }

    #[Test]
    public function varcharColumnWithCharsetAndCollation(): void
    {
        $column = Column::varchar('name')->length(255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci');

        $this->assertSame('`name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci', $column->toSql());
    }

    #[Test]
    public function charColumnWithLengthViaFactory(): void
    {
        $this->assertSame('`code` CHAR(3)', Column::char('code', 3)->toSql());
    }

    #[Test]
    public function varcharColumnWithLengthViaFactory(): void
    {
        $this->assertSame('`email` VARCHAR(100)', Column::varchar('email', 100)->toSql());
    }

    #[Test]
    public function textColumnWithLengthViaFactory(): void
    {
        $this->assertSame('`body` TEXT(1000)', Column::text('body', 1000)->toSql());
    }
}
