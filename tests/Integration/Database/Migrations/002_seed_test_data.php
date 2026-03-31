<?php

use Engine\Database\Contracts\ReversibleMigration;
use Engine\Database\Migrations\Migrator;
use Engine\Database\Query\Delete;
use Engine\Database\Query\Insert;

return new class implements ReversibleMigration {
    public function up(Migrator $migrator): void
    {
        $migrator->data(Insert::into('migration_test')->values(['name' => 'seeded']));
    }

    public function down(Migrator $migrator): void
    {
        $migrator->data(Delete::from('migration_test')->where('name', '=', 'seeded'));
    }
};
