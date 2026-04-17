<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use Engine\Database\Schema\Columns\JsonColumn;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('json-column')]
class JsonColumnTest extends TestCase
{
    #[Test]
    public function basicJsonColumnGeneratesCorrectSql(): void
    {
        $column = JsonColumn::make('data');

        $this->assertSame('`data` JSON', $column->toSql());
    }

    #[Test]
    public function jsonColumnWithNullable(): void
    {
        $column = JsonColumn::make('data')->nullable();

        $this->assertSame('`data` JSON NULL', $column->toSql());
    }

    #[Test]
    public function jsonColumnWithNotNull(): void
    {
        $column = JsonColumn::make('data')->notNull();

        $this->assertSame('`data` JSON NOT NULL', $column->toSql());
    }

    #[Test]
    public function jsonColumnWithComment(): void
    {
        $column = JsonColumn::make('metadata')->comment('Extra metadata');

        $this->assertSame("`metadata` JSON COMMENT 'Extra metadata'", $column->toSql());
    }

    #[Test]
    public function factoryMethodCreatesJsonColumn(): void
    {
        $column = Column::json('payload');

        $this->assertInstanceOf(JsonColumn::class, $column);
        $this->assertSame('`payload` JSON', $column->toSql());
    }

    #[Test]
    public function jsonColumnWithDefaultNull(): void
    {
        $column = JsonColumn::make('data')->default(null);

        $this->assertSame('`data` JSON DEFAULT NULL', $column->toSql());
    }
}
