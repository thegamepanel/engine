<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Fixtures;

enum IntBackedEnum: int
{
    case Ok = 200;

    case NotFound = 404;
}
