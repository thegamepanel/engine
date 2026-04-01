<?php
declare(strict_types=1);

namespace Engine\Database\Query;

use PDO;
use PDOStatement;

final class Result
{
    /**
     * @var array<string|int, mixed>
     */
    public readonly array $bindings;

    private PDOStatement $statement;

    /**
     * @var array<Row>|null
     */
    private ?array $rows = null;

    /**
     * @param PDOStatement             $statement
     * @param array<string|int, mixed> $bindings
     */
    public function __construct(PDOStatement $statement, array $bindings)
    {
        $this->bindings  = $bindings;
        $this->statement = $statement;
    }

    /**
     * Get the first row from the result.
     *
     * @return Row|null
     */
    public function first(): ?Row
    {
        $this->hydrate();

        return $this->rows[0] ?? null;
    }

    /**
     * Get all rows from the result.
     *
     * @return array<Row>
     */
    public function all(): array
    {
        $this->hydrate();

        return $this->rows;
    }

    /**
     * Iterate over each row in the result.
     *
     * @param callable(Row $row):void $callback
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

    /**
     * @phpstan-assert array<\Engine\Database\Query\Row> $this->rows
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
}
