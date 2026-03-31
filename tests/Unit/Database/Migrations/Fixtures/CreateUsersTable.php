<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Migrations\Fixtures;

use Engine\Database\Contracts\ReversibleMigration;
use Engine\Database\Migrations\Migrator;
use Engine\Database\Query\Delete;
use Engine\Database\Query\Insert;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Index;
use Engine\Database\Schema\Table;

final class CreateUsersTable implements ReversibleMigration
{
    public function up(Migrator $migrator): void
    {
        $migrator->schema(Table::create('users', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
            Column::string('name', 255),
            Column::string('email', 255),
            Index::primary('id'),
            Index::unique('users_email_unique', 'email'),
        ]));

        $migrator->data(Insert::into('users')->values([
            'name'  => 'Admin',
            'email' => 'admin@example.com',
        ]));
    }

    public function down(Migrator $migrator): void
    {
        $migrator->data(Delete::from('users'));

        $migrator->schema(Table::drop('users'));
    }
}
