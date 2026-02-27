<?php

namespace Engine\Database;

/**
 *
 */
final readonly class WriteResult
{
    private int $affectedRows;

    private ?string $lastInsertId;

    public function __construct(int $affectedRows, ?string $lastInsertId)
    {
        $this->affectedRows = $affectedRows;
        $this->lastInsertId = $lastInsertId;
    }

    /**
     * Get the number of affected rows.
     *
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->affectedRows;
    }

    /**
     * Get the last inserted ID.
     *
     * @return string|null
     */
    public function lastInsertId(): ?string
    {
        return $this->lastInsertId;
    }

    /**
     * Check if the write operation was successful.
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->affectedRows > 0;
    }
}
