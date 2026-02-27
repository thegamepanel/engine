<?php
declare(strict_types=1);

namespace Engine\Database\Exceptions;

use Throwable;

final class ConnectionException extends DatabaseException
{
    /**
     * @param string          $name
     * @param string          $message
     * @param \Throwable|null $previous
     */
    public function __construct(
        private readonly string $name,
        string                  $message = '',
        ?Throwable              $previous = null,
    )
    {
        parent::__construct(
            $message ?: $previous?->getMessage() ?? 'Unable to connect to the database "' . $this->name . '"',
            previous: $previous
        );
    }
}
