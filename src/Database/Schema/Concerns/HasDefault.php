<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Concerns;

trait HasDefault
{
    protected mixed $default = null;

    protected bool $defaultIsSet = false;

    /**
     * Add a default value for the column.
     *
     * @param mixed $default
     *
     * @return static
     */
    public function default(mixed $default): static
    {
        $this->default      = $default;
        $this->defaultIsSet = true;

        return $this;
    }

    /**
     * Check if a default value has been set.
     *
     * @return bool
     */
    protected function hasDefault(): bool
    {
        return $this->defaultIsSet;
    }

    /**
     * Get the default value.
     *
     * @return mixed
     */
    protected function getDefault(): mixed
    {
        return $this->default;
    }
}
