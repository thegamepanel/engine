<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\InvalidInvocationException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidInvocationExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = InvalidInvocationException::notPublic('MyClass', 'myMethod');

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_invalid_argument_exception(): void
    {
        $exception = InvalidInvocationException::notPublic('MyClass', 'myMethod');

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    #[Test]
    public function not_public_includes_class_and_method_in_message(): void
    {
        $exception = InvalidInvocationException::notPublic('App\\Service', 'doSomething');

        $this->assertStringContainsString('App\\Service', $exception->getMessage());
        $this->assertStringContainsString('doSomething', $exception->getMessage());
    }

    #[Test]
    public function already_initialised_includes_class_in_message(): void
    {
        $exception = InvalidInvocationException::alreadyInitialised('App\\Service');

        $this->assertStringContainsString('App\\Service', $exception->getMessage());
    }
}
