<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Concerns;

use Engine\Database\Query\Concerns\HasLimitClause;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('limit')]
class HasLimitClauseTest extends TestCase
{
    // -------------------------------------------------------------------------
    // limit()
    // -------------------------------------------------------------------------

    /**
     * - limit() produces a LIMIT clause.
     */
    #[Test]
    public function limitProducesLimitClause(): void
    {
        $query = $this->makeQuery();
        $query->limit(10);

        $this->assertSame('LIMIT 10', $query->sql());
    }

    // -------------------------------------------------------------------------
    // offset()
    // -------------------------------------------------------------------------

    /**
     * - offset() produces an OFFSET clause.
     */
    #[Test]
    public function offsetProducesOffsetClause(): void
    {
        $query = $this->makeQuery();
        $query->offset(5);

        $this->assertSame('OFFSET 5', $query->sql());
    }

    // -------------------------------------------------------------------------
    // limit() + offset()
    // -------------------------------------------------------------------------

    /**
     * - limit() and offset() together produce LIMIT ... OFFSET ... clause.
     */
    #[Test]
    public function limitAndOffsetProduceCombinedClause(): void
    {
        $query = $this->makeQuery();
        $query->limit(10);
        $query->offset(20);

        $this->assertSame('LIMIT 10 OFFSET 20', $query->sql());
    }

    /**
     * - offset() called before limit() produces the same result.
     */
    #[Test]
    public function offsetBeforeLimitProducesSameResult(): void
    {
        $query = $this->makeQuery();
        $query->offset(20);
        $query->limit(10);

        $this->assertSame('LIMIT 10 OFFSET 20', $query->sql());
    }

    // -------------------------------------------------------------------------
    // No clause
    // -------------------------------------------------------------------------

    /**
     * - No limit() or offset() calls produces an empty string.
     */
    #[Test]
    public function noLimitOrOffsetProducesEmptyString(): void
    {
        $query = $this->makeQuery();

        $this->assertSame('', $query->sql());
    }

    private function makeQuery(): object
    {
        return new class {
            use HasLimitClause;

            public function sql(): string
            {
                return ltrim($this->buildLimitClause());
            }
        };
    }
}
