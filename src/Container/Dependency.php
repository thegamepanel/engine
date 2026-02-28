<?php
declare(strict_types=1);

namespace Engine\Container;

use Engine\Container\Attributes\Named;
use Engine\Container\Contracts\Qualifier;
use Engine\Container\Contracts\Resolvable;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;

/**
 * @template TType of mixed
 * @template TQualifier of \Engine\Container\Contracts\Qualifier|null = null
 * @template TResolvable of \Engine\Container\Contracts\Resolvable|null = null
 */
final readonly class Dependency
{
    /**
     * @param string                                                                     $parameter
     * @param \ReflectionNamedType|\ReflectionUnionType|\ReflectionIntersectionType|null $type
     * @param bool                                                                       $optional
     * @param \Engine\Container\Attributes\Named|null                                    $name
     * @param TQualifier|null                                                            $qualifier
     * @param TResolvable|null                                                           $resolvable
     * @param bool                                                                       $hasDefault
     * @param TType|null                                                                 $default
     * @param bool                                                                       $liminal
     */
    public function __construct(
        public string                                                                  $parameter,
        public ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type,
        public bool                                                                    $optional = false,
        public ?Named                                                                  $name = null,
        public ?Qualifier                                                              $qualifier = null,
        public ?Resolvable                                                             $resolvable = null,
        public bool                                                                    $hasDefault = false,
        public mixed                                                                   $default = null,
        public bool                                                                    $liminal = false
    )
    {
    }
}
