<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Concerns;

use Engine\Database\Schema\Column;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('can-be-unsigned')]
class CanBeUnsignedTest extends TestCase
{
    #[Test]
    public function unsignedIntColumnIncludesUnsignedInSql(): void
    {
        $column = Column::int('age')->unsigned();

        $this->assertSame('`age` INT UNSIGNED', $column->toSql());
    }

    #[Test]
    public function unsignedDecimalColumnIncludesUnsignedInSql(): void
    {
        $column = Column::decimal('price')->unsigned();

        $this->assertSame('`price` DECIMAL UNSIGNED', $column->toSql());
    }

    #[Test]
    public function nonUnsignedColumnDoesNotIncludeUnsigned(): void
    {
        $column = Column::int('age');

        $this->assertSame('`age` INT', $column->toSql());
    }
}
