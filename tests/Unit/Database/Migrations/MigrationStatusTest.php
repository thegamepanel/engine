<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Migrations;

use Engine\Database\Migrations\MigrationStatus;
use Engine\Database\Query\Row;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('migrations'), Group('migration-status')]
class MigrationStatusTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Construction with defaults
    // -------------------------------------------------------------------------

    /**
     * - Construction with only required arguments defaults all flags to false.
     */
    #[Test]
    public function constructionWithDefaultsHasAllFlagsFalse(): void
    {
        $status = new MigrationStatus('001_create_users', 'core', 1);

        $this->assertSame('001_create_users', $status->migration);
        $this->assertSame('core', $status->module);
        $this->assertSame(1, $status->batch);
        $this->assertFalse($status->schema);
        $this->assertFalse($status->alter);
        $this->assertFalse($status->data);
        $this->assertFalse($status->errored);
    }

    // -------------------------------------------------------------------------
    // Construction with explicit flags
    // -------------------------------------------------------------------------

    /**
     * - Construction with explicit flags sets them correctly.
     */
    #[Test]
    public function constructionWithExplicitFlagsSetsThemCorrectly(): void
    {
        $status = new MigrationStatus('002_add_roles', 'auth', 3, true, false, true, true);

        $this->assertSame('002_add_roles', $status->migration);
        $this->assertSame('auth', $status->module);
        $this->assertSame(3, $status->batch);
        $this->assertTrue($status->schema);
        $this->assertFalse($status->alter);
        $this->assertTrue($status->data);
        $this->assertTrue($status->errored);
    }

    // -------------------------------------------------------------------------
    // phaseCompleted
    // -------------------------------------------------------------------------

    /**
     * - phaseCompleted returns the correct flag for the schema phase.
     */
    #[Test]
    public function phaseCompletedReturnsCorrectValueForSchema(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1, schema: true);

        $this->assertTrue($status->phaseCompleted('schema'));
        $this->assertFalse($status->phaseCompleted('alter'));
        $this->assertFalse($status->phaseCompleted('data'));
    }

    /**
     * - phaseCompleted returns the correct flag for the alter phase.
     */
    #[Test]
    public function phaseCompletedReturnsCorrectValueForAlter(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1, alter: true);

        $this->assertFalse($status->phaseCompleted('schema'));
        $this->assertTrue($status->phaseCompleted('alter'));
        $this->assertFalse($status->phaseCompleted('data'));
    }

    /**
     * - phaseCompleted returns the correct flag for the data phase.
     */
    #[Test]
    public function phaseCompletedReturnsCorrectValueForData(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1, data: true);

        $this->assertFalse($status->phaseCompleted('schema'));
        $this->assertFalse($status->phaseCompleted('alter'));
        $this->assertTrue($status->phaseCompleted('data'));
    }

    /**
     * - phaseCompleted returns false for an unknown phase.
     */
    #[Test]
    public function phaseCompletedReturnsFalseForUnknownPhase(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1, schema: true, alter: true, data: true);

        $this->assertFalse($status->phaseCompleted('unknown'));
    }

    // -------------------------------------------------------------------------
    // markPhase
    // -------------------------------------------------------------------------

    /**
     * - markPhase sets the schema flag to true.
     */
    #[Test]
    public function markPhaseSetsSchemaFlag(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1);

        $status->markPhase('schema');

        $this->assertTrue($status->schema);
        $this->assertFalse($status->alter);
        $this->assertFalse($status->data);
    }

    /**
     * - markPhase sets the alter flag to true.
     */
    #[Test]
    public function markPhaseSetsAlterFlag(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1);

        $status->markPhase('alter');

        $this->assertFalse($status->schema);
        $this->assertTrue($status->alter);
        $this->assertFalse($status->data);
    }

    /**
     * - markPhase sets the data flag to true.
     */
    #[Test]
    public function markPhaseSetsDataFlag(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1);

        $status->markPhase('data');

        $this->assertFalse($status->schema);
        $this->assertFalse($status->alter);
        $this->assertTrue($status->data);
    }

    /**
     * - markPhase with an unknown phase does not change any flags.
     */
    #[Test]
    public function markPhaseWithUnknownPhaseDoesNothing(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1);

        $status->markPhase('unknown');

        $this->assertFalse($status->schema);
        $this->assertFalse($status->alter);
        $this->assertFalse($status->data);
    }

    // -------------------------------------------------------------------------
    // markErrored
    // -------------------------------------------------------------------------

    /**
     * - markErrored sets the errored flag to true.
     */
    #[Test]
    public function markErroredSetsErroredFlag(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1);

        $status->markErrored();

        $this->assertTrue($status->errored);
    }

    // -------------------------------------------------------------------------
    // clearError
    // -------------------------------------------------------------------------

    /**
     * - clearError sets the errored flag to false.
     */
    #[Test]
    public function clearErrorResetsErroredFlag(): void
    {
        $status = new MigrationStatus('001_test', 'core', 1, errored: true);

        $status->clearError();

        $this->assertFalse($status->errored);
    }

    // -------------------------------------------------------------------------
    // fromRow
    // -------------------------------------------------------------------------

    /**
     * - fromRow creates a MigrationStatus from a Row object.
     */
    #[Test]
    public function fromRowCreatesInstanceFromDatabaseRow(): void
    {
        $row = new Row([
            'migration' => '001_create_users',
            'module'    => 'core',
            'batch'     => 2,
            'schema'    => 1,
            'alter'     => 0,
            'data'      => 1,
            'errored'   => 0,
        ]);

        $status = MigrationStatus::fromRow($row);

        $this->assertSame('001_create_users', $status->migration);
        $this->assertSame('core', $status->module);
        $this->assertSame(2, $status->batch);
        $this->assertTrue($status->schema);
        $this->assertFalse($status->alter);
        $this->assertTrue($status->data);
        $this->assertFalse($status->errored);
    }
}
