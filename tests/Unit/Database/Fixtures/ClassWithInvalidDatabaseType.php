<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Fixtures;

use Engine\Database\Attributes\Database;

final class ClassWithInvalidDatabaseType
{
    public function __construct(
        #[Database] public readonly string $connection,
    ) {
    }
}
