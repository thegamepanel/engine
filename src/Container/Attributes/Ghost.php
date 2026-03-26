<?php
declare(strict_types=1);

namespace Engine\Container\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Ghost implements Resolvable
{

}
