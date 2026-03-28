<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Fixtures;

/**
 * A minimal concrete implementation of {@see AbstractInterface}. Used as a
 * binding target to verify that the container can resolve an interface to a
 * concrete class, and that ghost resolver creates the proxy for the concrete
 * rather than the interface.
 */
class ConcreteClass implements AbstractInterface
{
}
