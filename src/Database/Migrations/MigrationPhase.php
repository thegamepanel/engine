<?php
declare(strict_types=1);

namespace Engine\Database\Migrations;

enum MigrationPhase: string
{
    case Schema = 'schema';

    case Alter = 'alter';

    case Data = 'data';
}
