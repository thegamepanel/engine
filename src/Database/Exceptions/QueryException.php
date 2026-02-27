<?php
declare(strict_types=1);

namespace Engine\Database\Exceptions;

use Throwable;

final class QueryException extends DatabaseException
{
    /**
     * @param array<int|string, mixed> $bindings
     */
    public function __construct(
        private readonly string $sql,
        private readonly array  $bindings,
        string                  $message = '',
        ?Throwable              $previous = null,
    )
    {
        parent::__construct(
            $message ?: $previous?->getMessage() ?? 'Unable to execute the query.',
            previous: $previous
        );
    }

    public
    function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return array<int|string, mixed>
     */
    public
    function getBindings(): array
    {
        return $this->bindings;
    }
}
