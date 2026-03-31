<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Migrations;

use Engine\Database\Migrations\MigrationPhase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('migrations'), Group('migration-phase')]
class MigrationPhaseTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Case count
    // -------------------------------------------------------------------------

    /**
     * - Has exactly three cases.
     */
    #[Test]
    public function hasExactlyThreeCases(): void
    {
        $cases = MigrationPhase::cases();

        $this->assertCount(3, $cases);
    }

    // -------------------------------------------------------------------------
    // String values
    // -------------------------------------------------------------------------

    /**
     * - Schema case has the string value 'schema'.
     */
    #[Test]
    public function schemaCaseHasCorrectStringValue(): void
    {
        $this->assertSame('schema', MigrationPhase::Schema->value);
    }

    /**
     * - Alter case has the string value 'alter'.
     */
    #[Test]
    public function alterCaseHasCorrectStringValue(): void
    {
        $this->assertSame('alter', MigrationPhase::Alter->value);
    }

    /**
     * - Data case has the string value 'data'.
     */
    #[Test]
    public function dataCaseHasCorrectStringValue(): void
    {
        $this->assertSame('data', MigrationPhase::Data->value);
    }

    // -------------------------------------------------------------------------
    // Construction from string
    // -------------------------------------------------------------------------

    /**
     * - Cases can be created from string values via from().
     */
    #[Test]
    public function casesCanBeCreatedFromStringValues(): void
    {
        $this->assertSame(MigrationPhase::Schema, MigrationPhase::from('schema'));
        $this->assertSame(MigrationPhase::Alter, MigrationPhase::from('alter'));
        $this->assertSame(MigrationPhase::Data, MigrationPhase::from('data'));
    }
}
