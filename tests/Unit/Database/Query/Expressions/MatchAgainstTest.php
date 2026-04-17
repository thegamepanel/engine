<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Query\Expressions;

use Engine\Database\Query\Expressions\MatchAgainst;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('database'), Group('match-against')]
class MatchAgainstTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Natural language mode
    // -------------------------------------------------------------------------

    /**
     * - MATCH AGAINST in natural language mode produces correct SQL.
     */
    #[Test]
    public function naturalLanguageModeProducesCorrectSql(): void
    {
        $expr = MatchAgainst::make(['title', 'body'], 'search term');

        $this->assertSame('MATCH(title, body) AGAINST(? IN NATURAL LANGUAGE MODE)', $expr->toSql());
        $this->assertSame(['search term'], $expr->getBindings());
    }

    /**
     * - MATCH AGAINST with a single column produces correct SQL.
     */
    #[Test]
    public function singleColumnProducesCorrectSql(): void
    {
        $expr = MatchAgainst::make(['title'], 'search');

        $this->assertSame('MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE)', $expr->toSql());
        $this->assertSame(['search'], $expr->getBindings());
    }

    // -------------------------------------------------------------------------
    // Boolean mode
    // -------------------------------------------------------------------------

    /**
     * - MATCH AGAINST in boolean mode produces correct SQL.
     */
    #[Test]
    public function booleanModeProducesCorrectSql(): void
    {
        $expr = MatchAgainst::make(['title', 'body'], '+required -excluded', 'boolean');

        $this->assertSame('MATCH(title, body) AGAINST(? IN BOOLEAN MODE)', $expr->toSql());
        $this->assertSame(['+required -excluded'], $expr->getBindings());
    }

    // -------------------------------------------------------------------------
    // Default mode
    // -------------------------------------------------------------------------

    /**
     * - MATCH AGAINST defaults to natural language mode when no mode specified.
     */
    #[Test]
    public function defaultModeIsNaturalLanguage(): void
    {
        $explicit = MatchAgainst::make(['title'], 'term', 'natural');
        $default  = MatchAgainst::make(['title'], 'term');

        $this->assertSame($explicit->toSql(), $default->toSql());
    }
}
