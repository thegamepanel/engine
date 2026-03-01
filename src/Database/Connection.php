<?php
declare(strict_types=1);

namespace Engine\Database;

use Engine\Database\Exceptions\DatabaseException;
use Engine\Database\Exceptions\QueryException;
use Engine\Database\Query\Contracts\Expression;
use JetBrains\PhpStorm\Language;
use PDO;
use PDOException;
use PDOStatement;
use Throwable;

/**
 *
 */
final class Connection
{
    public function __construct(
        public readonly string $name,
        private PDO            $pdo,
    )
    {
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string                   $query
     * @param array<int|string, mixed> $bindings
     *
     * @return \PDOStatement
     */
    private function statement(#[Language('GenericSQL')] string $query, array $bindings = []): PDOStatement
    {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($bindings);

            return $statement;
        } catch (PDOException $e) {
            throw new QueryException($query, $bindings, previous: $e);
        }
    }

    /**
     * Execute a query against the database and return the result.
     *
     * @param string|\Engine\Database\Query\Contracts\Expression $query
     * @param array<int|string, mixed>                            $bindings
     *
     * @return \Engine\Database\Result
     */
    public function query(#[Language('GenericSQL')] string|Expression $query, array $bindings = []): Result
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
     * @param string|\Engine\Database\Query\Contracts\Expression $query
     * @param array<int|string, mixed>                            $bindings
     *
     * @return \Engine\Database\WriteResult
     */
    public function execute(#[Language('GenericSQL')] string|Expression $query, array $bindings = []): WriteResult
    {
        if ($query instanceof Expression) {
            $bindings = $query->getBindings();
            $query    = $query->toSql();
        }

        try {
            return new WriteResult(
                affectedRows: $this->statement($query, $bindings)->rowCount(),
                lastInsertId: $this->pdo->lastInsertId() ?: null,
            );
        } catch (PDOException $e) {
            throw new QueryException($query, $bindings, previous: $e);
        }
    }

    /**
     * Execute a query against the database and return the result as a cursor.
     *
     * @param string|\Engine\Database\Query\Contracts\Expression $query
     * @param array<int|string, mixed>                            $bindings
     *
     * @return \Engine\Database\Cursor
     */
    public function stream(#[Language('GenericSQL')] string|Expression $query, array $bindings = []): Cursor
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
     *
     * @return void
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
     *
     * @return void
     */
    public function commit(): void
    {
        try {
            $this->pdo->commit();
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollback(): void
    {
        try {
            $this->pdo->rollBack();
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), previous: $e);
        }
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
}
