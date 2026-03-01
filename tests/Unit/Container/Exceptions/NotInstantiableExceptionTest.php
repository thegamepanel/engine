<?php
declare(strict_types=1);

namespace Tests\Unit\Container\Exceptions;

use Engine\Container\Contracts\ContainerException;
use Engine\Container\Exceptions\NotInstantiableException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class NotInstantiableExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_container_exception(): void
    {
        $exception = NotInstantiableException::make('SomeInterface');

        $this->assertInstanceOf(ContainerException::class, $exception);
    }

    #[Test]
    public function it_extends_invalid_argument_exception(): void
    {
        $exception = NotInstantiableException::make('SomeInterface');

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    #[Test]
    public function make_includes_class_and_preserves_previous(): void
    {
        $previous  = new RuntimeException('original');
        $exception = NotInstantiableException::make('App\\Contracts\\Service', $previous);

        $this->assertStringContainsString('App\\Contracts\\Service', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
