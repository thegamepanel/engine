<?php
declare(strict_types=1);

namespace Engine\Database;

use PDO;

final readonly class Connection
{
    public function __construct(
        public string $name,
        // @phpstan-ignore property.onlyWritten
        private PDO $pdo,
    ) {
    }
}
