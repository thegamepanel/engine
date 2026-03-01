<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Exceptions;

use Engine\Events\Contracts\EventException;
use Engine\Events\Exceptions\InvalidListenerException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InvalidListenerExceptionTest extends TestCase
{
    #[Test]
    public function it_implements_event_exception(): void
    {
        $exception = InvalidListenerException::wrongParameterCount('Foo', 'bar');

        $this->assertInstanceOf(EventException::class, $exception);
    }

    #[Test]
    public function it_extends_invalid_argument_exception(): void
    {
        $exception = InvalidListenerException::wrongParameterCount('Foo', 'bar');

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    #[Test]
    public function it_creates_wrong_parameter_count_message(): void
    {
        $exception = InvalidListenerException::wrongParameterCount('App\\Subscriber', 'handle');

        $this->assertStringContainsString('App\\Subscriber', $exception->getMessage());
        $this->assertStringContainsString('handle', $exception->getMessage());
    }

    #[Test]
    public function it_creates_invalid_event_class_message(): void
    {
        $exception = InvalidListenerException::invalidEventClass('App\\Subscriber', 'handle');

        $this->assertStringContainsString('App\\Subscriber', $exception->getMessage());
        $this->assertStringContainsString('handle', $exception->getMessage());
    }

    #[Test]
    public function it_creates_not_static_message(): void
    {
        $exception = InvalidListenerException::notStatic('App\\Subscriber', 'handle');

        $this->assertStringContainsString('App\\Subscriber', $exception->getMessage());
        $this->assertStringContainsString('handle', $exception->getMessage());
    }
}
