<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Concerns;

/**
 * Can Be Unsigned
 * ---------------
 *
 * Provides the ability to mark a numeric column as unsigned.
 */
trait CanBeUnsigned
{
    private bool $unsigned = false;

    /**
     * Mark the column as unsigned.
     *
     * @return static
     */
    public function unsigned(): static
    {
        $this->unsigned = true;

        return $this;
    }

    /**
     * Check if the column is unsigned.
     *
     * @return bool
     */
    protected function isUnsigned(): bool
    {
        return $this->unsigned;
    }
}
