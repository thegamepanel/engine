<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Concerns;

trait HasComment
{
    protected ?string $comment = null;

    /**
     * Add a comment to the column.
     *
     * @param string $comment
     *
     * @return static
     */
    public function comment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Check if a comment has been set.
     *
     * @return bool
     *
     * @phpstan-assert-if-true string $this->comment
     */
    protected function hasComment(): bool
    {
        return $this->comment !== null;
    }

    /**
     * Get the comment.
     *
     * @return string|null
     */
    protected function getComment(): ?string
    {
        return $this->comment;
    }
}
