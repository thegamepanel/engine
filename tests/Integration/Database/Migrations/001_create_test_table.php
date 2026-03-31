<?php

use Engine\Database\Contracts\ReversibleMigration;
use Engine\Database\Migrations\Migrator;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Index;
use Engine\Database\Schema\Table;

return new class implements ReversibleMigration {
    public function up(Migrator $migrator): void
    {
        $migrator->schema(Table::create('migration_test', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
            Column::string('name', 255),
            Index::primary('id'),
        ]));
    }

    public function down(Migrator $migrator): void
    {
        $migrator->schema(Table::drop('migration_test'));
    }
};
