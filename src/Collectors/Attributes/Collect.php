<?php
declare(strict_types=1);

namespace Engine\Collectors\Attributes;

use Attribute;
use Engine\Core\OperatingContext;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Collect
{
    public function __construct(
        public ?OperatingContext $operatingContext
    )
    {
    }
}
