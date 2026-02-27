<?php
declare(strict_types=1);

namespace Engine\Database;

use Generator;
use PDO;
use PDOStatement;

/**
 *
 */
final class Result
{
    private PDOStatement $statement;

    /**
     * @var array<string|int, mixed>
     *
     * @phpstan-ignore property.onlyWritten
     */
    private array $bindings;

    /**
     * @var array<\Engine\Database\Row>|null
     */
    private ?array $rows = null;

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
     * @return void
     *
     * @phpstan-assert array<\Engine\Database\Row> $this->rows
     */
    private function hydrate(): void
    {
        if ($this->rows === null) {
            $rows = [];

            /** @var array<string, mixed> $row */
            foreach ($this->statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $rows[] = new Row($row);
            }

            $this->rows = $rows;
        }
    }

    /**
     * Get the first row from the result.
     *
     * @return \Engine\Database\Row|null
     */
    public function first(): ?Row
    {
        $this->hydrate();

        return $this->rows[0] ?? null;
    }

    /**
     * Get all rows from the result.
     *
     * @return array<\Engine\Database\Row>
     */
    public function all(): array
    {
        $this->hydrate();

        return $this->rows;
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
        foreach ($this->all() as $row) {
            $callback($row);
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
