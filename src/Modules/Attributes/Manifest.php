<?php
declare(strict_types=1);

namespace Engine\Modules\Attributes;

use Attribute;
use Engine\Container\Contracts\Resolvable;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Manifest implements Resolvable
{
    public function __construct(
        public string $ident
    )
    {
    }
}
