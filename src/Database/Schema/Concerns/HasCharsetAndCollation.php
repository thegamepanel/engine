<?php
declare(strict_types=1);

namespace Engine\Database\Schema\Concerns;

trait HasCharsetAndCollation
{
    private string $charset;

    private string $collation;

    /**
     * Set the character set for the schema definition.
     *
     * @param string $charset
     *
     * @return static
     */
    public function charset(string $charset): static
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Set the collation for the schema definition.
     *
     * @param string $collation
     *
     * @return static
     */
    public function collation(string $collation): static
    {
        $this->collation = $collation;

        return $this;
    }

    /**
     * Check if a character set has been set.
     *
     * @return bool
     */
    protected function hasCharset(): bool
    {
        return isset($this->charset);
    }

    /**
     * Get the character set.
     *
     * @return string
     */
    protected function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Check if a collation has been set.
     *
     * @return bool
     */
    protected function hasCollation(): bool
    {
        return isset($this->collation);
    }

    /**
     * Get the collation.
     *
     * @return string
     */
    protected function getCollation(): string
    {
        return $this->collation;
    }
}
