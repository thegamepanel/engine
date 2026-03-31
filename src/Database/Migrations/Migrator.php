<?php
declare(strict_types=1);

namespace Engine\Database\Migrations;

use Engine\Database\Contracts\Query;
use Engine\Database\Contracts\Schema;

final readonly class Migrator
{
    public function __construct(
        private MigrationRunner $runner,
    ) {
    }

    /**
     * Register a schema phase expression.
     *
     * @param Schema $schema
     *
     * @return static
     */
    public function schema(Schema $schema): self
    {
        $this->runner->schema($schema);

        return $this;
    }

    /**
     * Register an alter phase expression.
     *
     * @param Schema $schema
     *
     * @return static
     */
    public function alter(Schema $schema): self
    {
        $this->runner->alter($schema);

        return $this;
    }

    /**
     * Register a data phase expression.
     *
     * @param Query $query
     *
     * @return static
     */
    public function data(Query $query): self
    {
        $this->runner->data($query);

        return $this;
    }
}
