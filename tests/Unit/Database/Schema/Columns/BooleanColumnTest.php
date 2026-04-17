<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use Engine\Database\Schema\Columns\BooleanColumn;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('boolean-column')]
class BooleanColumnTest extends TestCase
{
    #[Test]
    public function basicBooleanColumnGeneratesCorrectSql(): void
    {
        $column = BooleanColumn::make('is_active');

        $this->assertSame('`is_active` BOOLEAN', $column->toSql());
    }

    #[Test]
    public function booleanColumnWithNullable(): void
    {
        $column = BooleanColumn::make('is_active')->nullable();

        $this->assertSame('`is_active` BOOLEAN NULL', $column->toSql());
    }

    #[Test]
    public function booleanColumnWithDefault(): void
    {
        $column = BooleanColumn::make('is_active')->default(true);

        $this->assertSame('`is_active` BOOLEAN DEFAULT 1', $column->toSql());
    }

    #[Test]
    public function factoryMethodCreatesBooleanColumn(): void
    {
        $column = Column::boolean('is_active');

        $this->assertInstanceOf(BooleanColumn::class, $column);
        $this->assertSame('`is_active` BOOLEAN', $column->toSql());
    }
}
