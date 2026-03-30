<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Contracts\Expression;
use Engine\Database\Query\Expressions\Aggregate;
use Engine\Database\Query\Expressions\ColumnEqualTo;
use Engine\Database\Query\Expressions\ColumnGreaterThen;
use Engine\Database\Query\Expressions\ColumnGreaterThenOrEqualTo;
use Engine\Database\Query\Expressions\ColumnIn;
use Engine\Database\Query\Expressions\ColumnIs;
use Engine\Database\Query\Expressions\ColumnIsNotNull;
use Engine\Database\Query\Expressions\ColumnIsNull;
use Engine\Database\Query\Expressions\ColumnLessThan;
use Engine\Database\Query\Expressions\ColumnLessThanOrEqualTo;
use Engine\Database\Query\Expressions\ColumnNotEqualTo;
use Engine\Database\Query\Expressions\ColumnNotIn;
use Engine\Database\Query\Expressions\MatchAgainst;
use Engine\Database\Query\Expressions\RawExpression;
use InvalidArgumentException;

final class Expressions
{
    public static function whereColumn(string $operator, string $column, mixed $value): Expression
    {
        return match (strtolower($operator)) {
            '='           => ColumnEqualTo::make($column, $value),
            '<'           => ColumnLessThan::make($column, $value),
            '>'           => ColumnGreaterThen::make($column, $value),
            '<='          => ColumnLessThanOrEqualTo::make($column, $value),
            '>='          => ColumnGreaterThenOrEqualTo::make($column, $value),
            'is'          => ColumnIs::make($column, $value),
            'is null'     => ColumnIsNull::make($column),
            'is not null' => ColumnIsNotNull::make($column),
            '!='          => ColumnNotEqualTo::make($column, $value),
            'in'          => ColumnIn::make($column, $value),    // @phpstan-ignore-line
            'not in'      => ColumnNotIn::make($column, $value), // @phpstan-ignore-line
            default       => throw new InvalidArgumentException(sprintf('Invalid operator "%s".', $operator)),
        };
    }

    /**
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return Expression
     */
    public static function raw(string $sql, array $bindings): Expression
    {
        return RawExpression::make($sql, $bindings);
    }

    /**
     * @param string|Expression $column
     *
     * @return Expression
     */
    public static function count(Expression|string $column = '*'): Expression
    {
        return Aggregate::make('COUNT', $column);
    }

    /**
     * @param string|Expression $column
     *
     * @return Expression
     */
    public static function sum(Expression|string $column): Expression
    {
        return Aggregate::make('SUM', $column);
    }

    /**
     * @param string|Expression $column
     *
     * @return Expression
     */
    public static function min(Expression|string $column): Expression
    {
        return Aggregate::make('MIN', $column);
    }

    /**
     * @param string|Expression $column
     *
     * @return Expression
     */
    public static function max(Expression|string $column): Expression
    {
        return Aggregate::make('MAX', $column);
    }

    /**
     * @param string|Expression $column
     *
     * @return Expression
     */
    public static function avg(Expression|string $column): Expression
    {
        return Aggregate::make('AVG', $column);
    }

    /**
     * @param array<string> $columns
     * @param string        $value
     *
     * @return Expression
     */
    public static function match(array $columns, string $value): Expression
    {
        return MatchAgainst::make($columns, $value);
    }

    /**
     * @param array<string> $columns
     * @param string        $value
     *
     * @return Expression
     */
    public static function matchBoolean(array $columns, string $value): Expression
    {
        return MatchAgainst::make($columns, $value, 'boolean');
    }
}
