<?php
declare(strict_types=1);

namespace Engine\Container\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS)]
final readonly class Liminal implements Resolvable
{

}
