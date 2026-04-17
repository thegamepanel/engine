<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('int-column')]
class IntColumnTest extends TestCase
{
    #[Test]
    public function intColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`id` INT', Column::int('id')->toSql());
    }

    #[Test]
    public function intColumnWithLength(): void
    {
        $this->assertSame('`id` INT(11)', Column::int('id', 11)->toSql());
    }

    #[Test]
    public function intColumnWithUnsigned(): void
    {
        $this->assertSame('`id` INT UNSIGNED', Column::int('id')->unsigned()->toSql());
    }

    #[Test]
    public function intColumnWithLengthAndUnsigned(): void
    {
        $this->assertSame('`id` INT(11) UNSIGNED', Column::int('id', 11)->unsigned()->toSql());
    }

    #[Test]
    public function intColumnWithAutoIncrement(): void
    {
        $this->assertSame('`id` INT UNSIGNED AUTO_INCREMENT', Column::int('id')->unsigned()->autoIncrement()->toSql());
    }

    #[Test]
    public function intColumnWithIntDefault(): void
    {
        $this->assertSame('`age` INT DEFAULT 25', Column::int('age')->default(25)->toSql());
    }

    #[Test]
    public function intColumnWithFloatDefault(): void
    {
        $this->assertSame('`score` INT DEFAULT 9.5', Column::int('score')->default(9.5)->toSql());
    }

    #[Test]
    public function tinyIntColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`flag` TINYINT', Column::tinyInt('flag')->toSql());
    }

    #[Test]
    public function tinyIntColumnWithLength(): void
    {
        $this->assertSame('`flag` TINYINT(1)', Column::tinyInt('flag', 1)->toSql());
    }

    #[Test]
    public function smallIntColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`port` SMALLINT', Column::smallInt('port')->toSql());
    }

    #[Test]
    public function smallIntColumnWithLength(): void
    {
        $this->assertSame('`port` SMALLINT(5)', Column::smallInt('port', 5)->toSql());
    }

    #[Test]
    public function mediumIntColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`views` MEDIUMINT', Column::mediumInt('views')->toSql());
    }

    #[Test]
    public function mediumIntColumnWithLength(): void
    {
        $this->assertSame('`views` MEDIUMINT(8)', Column::mediumInt('views', 8)->toSql());
    }

    #[Test]
    public function bigIntColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`id` BIGINT', Column::bigInt('id')->toSql());
    }

    #[Test]
    public function bigIntColumnWithLength(): void
    {
        $this->assertSame('`id` BIGINT(20)', Column::bigInt('id', 20)->toSql());
    }

    #[Test]
    public function bigIntColumnWithUnsigned(): void
    {
        $this->assertSame('`id` BIGINT UNSIGNED', Column::bigInt('id')->unsigned()->toSql());
    }

    #[Test]
    public function lengthCanBeSetAfterConstruction(): void
    {
        $column = Column::int('id')->length(11);

        $this->assertSame('`id` INT(11)', $column->toSql());
    }

    #[Test]
    public function tinyIntColumnWithUnsignedAutoIncrement(): void
    {
        $this->assertSame('`id` TINYINT UNSIGNED AUTO_INCREMENT', Column::tinyInt('id')->unsigned()->autoIncrement()->toSql());
    }
}
