<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\InvalidClassException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InvalidClassExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = InvalidClassException::make('BadClass');

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_runtime_exception(): void
    {
        $exception = InvalidClassException::make('BadClass');

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    #[Test]
    public function make_includes_class_and_preserves_previous(): void
    {
        $previous  = new RuntimeException('original');
        $exception = InvalidClassException::make('App\\BadClass', $previous);

        $this->assertStringContainsString('App\\BadClass', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
