<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\DependencyResolutionException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DependencyResolutionExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = DependencyResolutionException::cannotResolve('string');

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_runtime_exception(): void
    {
        $exception = DependencyResolutionException::cannotResolve('string');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function cannot_resolve_includes_type_in_message(): void
    {
        $exception = DependencyResolutionException::cannotResolve('string');

        $this->assertStringContainsString('string', $exception->getMessage());
    }

    #[Test]
    public function cannot_resolve_preserves_previous_exception(): void
    {
        $previous  = new RuntimeException('original');
        $exception = DependencyResolutionException::cannotResolve('string', $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function intersection_includes_type_in_message(): void
    {
        $exception = DependencyResolutionException::intersection('Foo&Bar');

        $this->assertStringContainsString('Foo&Bar', $exception->getMessage());
    }

    #[Test]
    public function intersection_no_binding_includes_type_in_message(): void
    {
        $exception = DependencyResolutionException::intersectionNoBinding('Foo&Bar');

        $this->assertStringContainsString('Foo&Bar', $exception->getMessage());
    }

    #[Test]
    public function union_includes_type_in_message(): void
    {
        $exception = DependencyResolutionException::union('Foo|Bar');

        $this->assertStringContainsString('Foo|Bar', $exception->getMessage());
    }

    #[Test]
    public function ghost_includes_type_in_message(): void
    {
        $exception = DependencyResolutionException::ghost('SomeClass');

        $this->assertStringContainsString('SomeClass', $exception->getMessage());
    }
}
