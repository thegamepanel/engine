<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Fixtures;

use Engine\Database\Attributes\Database;
use Engine\Database\Connection;

final class ClassWithDatabaseDependency
{
    public function __construct(
        #[Database] public readonly Connection $default,
        #[Database('secondary')] public readonly Connection $secondary,
    ) {
    }
}
