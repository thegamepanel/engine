<?php
declare(strict_types=1);

namespace Engine\Database\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;
use Engine\Database\Connection;
use Engine\Database\DatabaseResolver;

/**
 * Database Resolvable
 * -------------------
 *
 * Marks a parameter so that it is resolved using {@see DatabaseResolver}, and
 * given a valid database connection.
 *
 * Requires that the parameter is type hinted as {@see Connection}.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Database implements Resolvable
{
    public function __construct(
        public ?string $name = null,
    ) {
    }
}
