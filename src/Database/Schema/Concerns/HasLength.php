<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Concerns;

trait HasLength
{
    private ?int $length = null;

    /**
     * Set the length of the column.
     *
     * @param int $length
     *
     * @return static
     */
    public function length(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get the length.
     *
     * @return int|null
     */
    protected function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @return bool
     *
     * @phpstan-assert-if-true int $this->length
     */
    protected function hasLength(): bool
    {
        return $this->length !== null;
    }
}
