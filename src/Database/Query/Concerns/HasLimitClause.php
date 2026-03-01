<?php
declare(strict_types=1);

namespace Engine\Database\Query\Concerns;

trait HasLimitClause
{
    private ?int $limitValue  = null;
    private ?int $offsetValue = null;

    /**
     * Set the limit for the query.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limitValue = $limit;

        return $this;
    }

    /**
     * Set the offset for the query.
     *
     * @param int $offset
     *
     * @return $this
     */
    public function offset(int $offset): static
    {
        $this->offsetValue = $offset;

        return $this;
    }

    private function buildLimitClause(): string
    {
        $sql = '';

        if ($this->limitValue !== null) {
            $sql .= " LIMIT {$this->limitValue}";
        }

        if ($this->offsetValue !== null) {
            $sql .= " OFFSET {$this->offsetValue}";
        }

        return $sql;
    }
}
