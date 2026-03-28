<?php
declare(strict_types=1);

namespace Engine\Container\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;

/**
 * Lazy Attribute
 * --------------
 *
 * Marks a parameter or class for lazy proxy resolution. The container will
 * return an uninitialized proxy whose underlying instance is not created
 * until the proxy is first accessed.
 *
 * @package Engine\Container
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_CLASS)]
final readonly class Lazy implements Resolvable
{

}
