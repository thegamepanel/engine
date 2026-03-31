<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Migrations\Fixtures;

use Engine\Database\Contracts\Migration;
use Engine\Database\Migrations\Migrator;
use Engine\Database\Query\Insert;

final class DataOnlyMigration implements Migration
{
    public function up(Migrator $migrator): void
    {
        $migrator->data(Insert::into('settings')->values([
            'key'   => 'site_name',
            'value' => 'Test Site',
        ]));
    }
}
