<?php
declare(strict_types=1);

namespace Engine\Database;

use Engine\Database\Contracts\Expression;
use Engine\Database\Exceptions\DatabaseException;
use Engine\Database\Exceptions\QueryException;
use Engine\Database\Query\Cursor;
use Engine\Database\Query\Result;
use Engine\Database\Query\WriteResult;
use JetBrains\PhpStorm\Language;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;

final readonly class Connection
{
    public function __construct(
        public string $name,
        private PDO $pdo,
    ) {
    }

    /**
     * Execute a query against the database and return the result.
     *
     * @param string|Expression        $query
     * @param array<int|string, mixed> $bindings
     *
     * @return Result
     */
    public function query(#[Language('GenericSQL')] Expression|string $query, array $bindings = []): Result
    {
        if ($query instanceof Expression) {
            $bindings = $query->getBindings();
            $query    = $query->toSql();
        }

        return new Result($this->statement($query, $bindings), $bindings);
    }

    /**
     * Execute a query against the database and return the number of affected rows.
     *
     * @param string|Expression        $query
     * @param array<int|string, mixed> $bindings
     *
     * @return WriteResult
     */
    public function execute(#[Language('GenericSQL')] Expression|string $query, array $bindings = []): WriteResult
    {
        if ($query instanceof Expression) {
            $bindings = $query->getBindings();
            $query    = $query->toSql();
        }

        try {
            return new WriteResult(
                $this->statement($query, $bindings)->rowCount(),
                $this->pdo->lastInsertId() ?: null,
                $bindings,
            );
            // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            throw new QueryException($query, $bindings, previous: $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Execute a query against the database and return the result as a cursor.
     *
     * @param string|Expression        $query
     * @param array<int|string, mixed> $bindings
     *
     * @return Cursor
     */
    public function stream(#[Language('GenericSQL')] Expression|string $query, array $bindings = []): Cursor
    {
        if ($query instanceof Expression) {
            $bindings = $query->getBindings();
            $query    = $query->toSql();
        }

        return new Cursor($this->statement($query, $bindings), $bindings);
    }

    /**
     * Begin a new database transaction.
     *
     * @template TReturn of mixed
     *
     * @param callable(self): TReturn $callback
     *
     * @return TReturn
     *
     * @throws Throwable
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Start a new database transaction.
     */
    public function beginTransaction(): void
    {
        try {
            $this->pdo->beginTransaction();
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Commit the active database transaction.
     */
    public function commit(): void
    {
        try {
            $this->pdo->commit();
            // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), previous: $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Rollback the active database transaction.
     */
    public function rollback(): void
    {
        try {
            $this->pdo->rollBack();
            // @codeCoverageIgnoreStart
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), previous: $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Determine if the connection is currently in a transaction.
     *
     * @return bool
     */
    public function isInTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @param string                   $query
     * @param array<int|string, mixed> $bindings
     *
     * @return PDOStatement
     */
    private function statement(#[Language('GenericSQL')] string $query, array $bindings = []): PDOStatement
    {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($this->processBindings($bindings));

            return $statement;
        } catch (PDOException $e) {
            throw new QueryException($query, $bindings, previous: $e);
        }
    }

    /**
     * Process the given bindings.
     *
     * Processes bindings to better prepare them for execution. Currently,
     * converts <code>bool</code> to <code>int</code>.
     *
     * @param array<int|string, mixed> $bindings
     *
     * @return array<int|string, mixed>
     */
    private function processBindings(array $bindings): array
    {
        return array_map(
            static fn (mixed $value): mixed => is_bool($value) ? (int) $value : $value,
            $bindings,
        );
    }
}
