<?php
declare(strict_types=1);

namespace Engine\Database\Query\Exceptions;

use InvalidArgumentException;

final class InvalidExpressionException extends InvalidArgumentException
{
    public static function emptyGroupedCondition(): self
    {
        return new self('Grouped condition closure must add at least one condition.');
    }

    public static function emptyInClause(string $column): self
    {
        return new self(
            sprintf('Cannot use an empty array for an IN clause on column "%s".', $column)
        );
    }
}
