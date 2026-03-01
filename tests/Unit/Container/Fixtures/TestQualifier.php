<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

use Attribute;
use Engine\Container\Contracts\Qualifier;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class TestQualifier implements Qualifier
{
}
