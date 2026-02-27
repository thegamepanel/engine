<?php
declare(strict_types=1);

namespace Engine\Database;

use Generator;
use PDO;
use PDOStatement;

/**
 *
 */
final class Cursor
{
    private PDOStatement $statement;

    /**
     * @var array<string|int, mixed>
     *
     * @phpstan-ignore property.onlyWritten
     */
    private array $bindings;

    /**
     * @param \PDOStatement            $statement
     * @param array<string|int, mixed> $bindings
     */
    public function __construct(PDOStatement $statement, array $bindings)
    {
        $this->bindings  = $bindings;
        $this->statement = $statement;
    }

    /**
     * Iterate over each row in the result.
     *
     * @param callable(\Engine\Database\Row $row):void $callback
     *
     * @return void
     */
    public function each(callable $callback): void
    {
        while ($row = $this->statement->fetch(PDO::FETCH_ASSOC)) {
            /** @var array<string, mixed> $row */
            $callback(new Row($row));
        }
    }

    /**
     * Get a generator for each row in the result.
     *
     * @return \Generator
     */
    public function rows(): Generator
    {
        while ($row = $this->statement->fetch(PDO::FETCH_ASSOC)) {
            /** @var array<string, mixed> $row */
            yield new Row($row);
        }
    }

    /**
     * Get the number of rows in the result.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->statement->rowCount();
    }

    /**
     * Determine if the result is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }
}
