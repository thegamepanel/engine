<?php
declare(strict_types=1);

namespace Engine\Modules\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Boot
{

}
