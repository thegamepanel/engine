<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Migrations\Fixtures;

use Engine\Database\Contracts\Migration;
use Engine\Database\Migrations\Migrator;
use Engine\Database\Schema\Column;
use Engine\Database\Schema\Index;
use Engine\Database\Schema\Table;

final class CreatePostsTable implements Migration
{
    public function up(Migrator $migrator): void
    {
        $migrator->schema(Table::create('posts', [
            Column::bigInt('id')->unsigned()->autoIncrement(),
            Column::bigInt('user_id')->unsigned(),
            Column::string('title', 255),
            Index::primary('id'),
        ]));

        $migrator->alter(Table::alter('posts', function ($blueprint) {
            $blueprint->add(
                Index::foreign('posts_user_id_fk', 'user_id')
                    ->references('users', 'id')
                    ->onDelete('cascade'),
            );
        }));
    }
}
