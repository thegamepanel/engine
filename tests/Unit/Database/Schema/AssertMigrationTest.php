<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use Engine\Database\Exceptions\InvalidSchemaException;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Drop;
use Engine\Database\Schema\Indexes\ForeignKey;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('assert-migration')]
class AssertMigrationTest extends TestCase
{
    // -------------------------------------------------------------------------
    // incompatibleModifier
    // -------------------------------------------------------------------------

    #[Test]
    public function stringColumnLengthThrowsForIncompatibleType(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Column::tinytext('body')->length(100);
    }

    #[Test]
    public function binaryColumnLengthThrowsForIncompatibleType(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Column::tinyblob('data')->length(100);
    }

    #[Test]
    public function temporalColumnPrecisionThrowsForIncompatibleType(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Column::date('created_at')->precision(6);
    }

    #[Test]
    public function temporalColumnDefaultCurrentTimestampThrowsForIncompatibleType(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Column::date('created_at')->defaultCurrentTimestamp();
    }

    #[Test]
    public function temporalColumnOnUpdateCurrentTimestampThrowsForIncompatibleType(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Column::date('created_at')->onUpdateCurrentTimestamp();
    }

    #[Test]
    public function dropIfExistsThrowsForIncompatibleType(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Drop::column('name')->ifExists();
    }

    #[Test]
    public function dropTemporaryThrowsForIncompatibleType(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Drop::column('name')->temporary();
    }

    // -------------------------------------------------------------------------
    // requiresGeneratedColumn
    // -------------------------------------------------------------------------

    #[Test]
    public function virtualThrowsOnNonGeneratedColumn(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Column::int('id')->virtual();
    }

    #[Test]
    public function storedThrowsOnNonGeneratedColumn(): void
    {
        $this->expectException(InvalidSchemaException::class);

        Column::int('id')->stored();
    }

    // -------------------------------------------------------------------------
    // incompleteForeignKey
    // -------------------------------------------------------------------------

    #[Test]
    public function foreignKeyWithoutOnThrows(): void
    {
        $this->expectException(InvalidSchemaException::class);

        ForeignKey::make('fk_user', ['user_id'])->references('id')->toSql();
    }

    #[Test]
    public function foreignKeyWithoutReferencesThrows(): void
    {
        $this->expectException(InvalidSchemaException::class);

        ForeignKey::make('fk_user', ['user_id'])->on('users')->toSql();
    }

    // -------------------------------------------------------------------------
    // Valid usage (no exception)
    // -------------------------------------------------------------------------

    #[Test]
    public function dropTableIfExistsDoesNotThrow(): void
    {
        $drop = Drop::table('users')->ifExists();

        $this->assertSame('DROP TABLE IF EXISTS `users`', $drop->toSql());
    }

    #[Test]
    public function dropDatabaseIfExistsDoesNotThrow(): void
    {
        $drop = Drop::database('mydb')->ifExists();

        $this->assertSame('DROP DATABASE IF EXISTS `mydb`', $drop->toSql());
    }

    #[Test]
    public function foreignKeyWithValidConfigDoesNotThrow(): void
    {
        $fk = ForeignKey::make('fk_user', ['user_id'])->on('users')->references('id');

        $this->assertSame(
            'CONSTRAINT `fk_user`  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)',
            $fk->toSql(),
        );
    }
}
