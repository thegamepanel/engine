<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('binary-column')]
class BinaryColumnTest extends TestCase
{
    #[Test]
    public function binaryColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`data` BINARY', Column::binary('data')->toSql());
    }

    #[Test]
    public function binaryColumnWithLength(): void
    {
        $this->assertSame('`data` BINARY(16)', Column::binary('data')->length(16)->toSql());
    }

    #[Test]
    public function blobColumnWithLength(): void
    {
        $this->assertSame('`data` BLOB(1000)', Column::blob('data')->length(1000)->toSql());
    }

    #[Test]
    public function varbinaryColumnWithLength(): void
    {
        $this->assertSame('`data` VARBINARY(255)', Column::varbinary('data')->length(255)->toSql());
    }

    #[Test]
    public function tinyblobColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`data` TINYBLOB', Column::tinyblob('data')->toSql());
    }

    #[Test]
    public function mediumblobColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`data` MEDIUMBLOB', Column::mediumblob('data')->toSql());
    }

    #[Test]
    public function longblobColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`data` LONGBLOB', Column::longblob('data')->toSql());
    }

    #[Test]
    public function blobColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`data` BLOB', Column::blob('data')->toSql());
    }

    #[Test]
    public function varbinaryColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`data` VARBINARY', Column::varbinary('data')->toSql());
    }
}
