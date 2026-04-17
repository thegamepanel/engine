<?php
declare(strict_types=1);

namespace Tests\Unit\Database\Schema\Fixtures;

enum StatusEnum: string
{
    case Active = 'active';

    case Inactive = 'inactive';
}
