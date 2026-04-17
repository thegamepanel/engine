<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('decimal-column')]
class DecimalColumnTest extends TestCase
{
    #[Test]
    public function decimalColumnWithoutPrecision(): void
    {
        $this->assertSame('`price` DECIMAL', Column::decimal('price')->toSql());
    }

    #[Test]
    public function decimalColumnWithLength(): void
    {
        $this->assertSame('`price` DECIMAL(10)', Column::decimal('price', 10)->toSql());
    }

    #[Test]
    public function decimalColumnWithLengthAndDecimals(): void
    {
        $this->assertSame('`price` DECIMAL(10,2)', Column::decimal('price', 10, 2)->toSql());
    }

    #[Test]
    public function decimalColumnWithDecimalsOnly(): void
    {
        $this->assertSame('`price` DECIMAL(2)', Column::decimal('price', null, 2)->toSql());
    }

    #[Test]
    public function decimalColumnWithUnsigned(): void
    {
        $this->assertSame('`price` DECIMAL UNSIGNED', Column::decimal('price')->unsigned()->toSql());
    }

    #[Test]
    public function decimalColumnWithLengthAndUnsigned(): void
    {
        $this->assertSame('`price` DECIMAL(10,2) UNSIGNED', Column::decimal('price', 10, 2)->unsigned()->toSql());
    }

    #[Test]
    public function lengthCanBeSetAfterConstruction(): void
    {
        $column = Column::decimal('price')->length(10);

        $this->assertSame('`price` DECIMAL(10)', $column->toSql());
    }

    #[Test]
    public function floatColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`rate` FLOAT', Column::float('rate')->toSql());
    }

    #[Test]
    public function doubleColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`amount` DOUBLE', Column::double('amount')->toSql());
    }
}
