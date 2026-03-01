<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use Engine\Database\Query\Contracts\Expression;
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
use Engine\Database\Query\Expressions\RawExpression;

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
        };
    }

    /**
     * @param string                   $sql
     * @param array<int|string, mixed> $bindings
     *
     * @return \Engine\Database\Query\Contracts\Expression
     */
    public static function raw(string $sql, array $bindings): Expression
    {
        return RawExpression::make($sql, $bindings);
    }
}
