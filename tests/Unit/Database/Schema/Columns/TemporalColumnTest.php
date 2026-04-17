<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Columns;

use Engine\Database\Schema\Column;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('temporal-column')]
class TemporalColumnTest extends TestCase
{
    #[Test]
    public function dateColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`dob` date', Column::date('dob')->toSql());
    }

    #[Test]
    public function datetimeColumnWithPrecision(): void
    {
        $this->assertSame('`created_at` datetime(6)', Column::datetime('created_at')->precision(6)->toSql());
    }

    #[Test]
    public function timestampColumnWithPrecision(): void
    {
        $this->assertSame('`updated_at` timestamp(3)', Column::timestamp('updated_at')->precision(3)->toSql());
    }

    #[Test]
    public function timeColumnWithPrecision(): void
    {
        $this->assertSame('`duration` time(2)', Column::time('duration')->precision(2)->toSql());
    }

    #[Test]
    public function timestampWithDefaultCurrentTimestamp(): void
    {
        $column = Column::timestamp('created_at')->defaultCurrentTimestamp();

        $this->assertSame('`created_at` timestamp DEFAULT CURRENT_TIMESTAMP', $column->toSql());
    }

    #[Test]
    public function timestampWithOnUpdateCurrentTimestamp(): void
    {
        $column = Column::timestamp('updated_at')->onUpdateCurrentTimestamp();

        $this->assertSame('`updated_at` timestamp ON UPDATE CURRENT_TIMESTAMP', $column->toSql());
    }

    #[Test]
    public function datetimeWithDefaultCurrentTimestamp(): void
    {
        $column = Column::datetime('created_at')->defaultCurrentTimestamp();

        $this->assertSame('`created_at` datetime DEFAULT CURRENT_TIMESTAMP', $column->toSql());
    }

    #[Test]
    public function datetimeWithOnUpdateCurrentTimestamp(): void
    {
        $column = Column::datetime('updated_at')->onUpdateCurrentTimestamp();

        $this->assertSame('`updated_at` datetime ON UPDATE CURRENT_TIMESTAMP', $column->toSql());
    }

    #[Test]
    public function yearColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`birth_year` year', Column::year('birth_year')->toSql());
    }

    #[Test]
    public function yearColumnViaFactoryMethod(): void
    {
        $column = Column::year('founded');

        $this->assertSame('`founded` year', $column->toSql());
    }

    #[Test]
    public function timeColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`duration` time', Column::time('duration')->toSql());
    }

    #[Test]
    public function timestampColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`created_at` timestamp', Column::timestamp('created_at')->toSql());
    }

    #[Test]
    public function datetimeColumnGeneratesCorrectSql(): void
    {
        $this->assertSame('`created_at` datetime', Column::datetime('created_at')->toSql());
    }

    #[Test]
    public function timestampWithDefaultCurrentTimestampAndOnUpdate(): void
    {
        $column = Column::timestamp('updated_at')->defaultCurrentTimestamp()->onUpdateCurrentTimestamp();

        $this->assertSame('`updated_at` timestamp ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP', $column->toSql());
    }
}
