<?php
declare(strict_types=1);

namespace Engine\Container\Attributes;

use Attribute;

/**
 * Named Attribute
 *
 * Provides a 'name' for a dependency that can be used to determine which of
 * multiple possible candidates should be injected.
 *
 * @package Engine\Container
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Named
{
    public function __construct(
        public string $name
    )
    {
    }
}
