<?php
declare(strict_types=1);

namespace Engine\Database\Exceptions;

use Throwable;

final class ConnectionException extends DatabaseException
{
    public static function cannotConnect(string $name, Throwable $previous): self
    {
        return new self($name, previous: $previous);
    }

    public static function noConfig(string $name): self
    {
        return new self($name, sprintf(
            'No configuration found for the database connection "%s"',
            $name,
        ));
    }

    /**
     * @param string         $name
     * @param string         $message
     * @param Throwable|null $previous
     */
    public function __construct(
        private readonly string $name,
        string                  $message = '',
        ?Throwable              $previous = null,
    ) {
        parent::__construct(
            $message ?: $previous?->getMessage() ?? 'Unable to connect to the database "' . $this->name . '"',
            previous: $previous,
        );
    }
}
