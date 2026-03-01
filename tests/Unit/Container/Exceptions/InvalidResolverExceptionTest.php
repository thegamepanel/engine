<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\InvalidResolverException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidResolverExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = InvalidResolverException::noDefault();

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_invalid_argument_exception(): void
    {
        $exception = InvalidResolverException::noDefault();

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    #[Test]
    public function resolvable_includes_class_in_message(): void
    {
        $exception = InvalidResolverException::resolvable('App\\BadResolvable');

        $this->assertStringContainsString('App\\BadResolvable', $exception->getMessage());
    }

    #[Test]
    public function resolver_includes_class_in_message(): void
    {
        $exception = InvalidResolverException::resolver('App\\BadResolver');

        $this->assertStringContainsString('App\\BadResolver', $exception->getMessage());
    }

    #[Test]
    public function no_default_has_expected_message(): void
    {
        $exception = InvalidResolverException::noDefault();

        $this->assertStringContainsString('no default resolver', $exception->getMessage());
    }
}
