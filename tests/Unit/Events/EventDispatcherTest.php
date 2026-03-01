<?php
declare(strict_types=1);

namespace Tests\Unit\Events;

use Engine\Events\Contracts\EventDispatcher as EventDispatcherContract;
use Engine\Events\EventDispatcher;
use Engine\Events\Registries\RuntimeListenerRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Unit\Events\Fixtures\BaseEvent;
use Tests\Unit\Events\Fixtures\ExtendedEvent;
use Tests\Unit\Events\Fixtures\UserCreated;
use Tests\Unit\Events\Fixtures\UserDeleted;

final class EventDispatcherTest extends TestCase
{
    private RuntimeListenerRegistry $registry;
    private EventDispatcher         $dispatcher;

    protected function setUp(): void
    {
        $this->registry   = new RuntimeListenerRegistry();
        $this->dispatcher = new EventDispatcher($this->registry);
    }

    // ── Contract ──────────────────────────────────────────────

    #[Test]
    public function it_implements_event_dispatcher_contract(): void
    {
        $this->assertInstanceOf(EventDispatcherContract::class, $this->dispatcher);
    }

    // ── Dispatching ───────────────────────────────────────────

    #[Test]
    public function it_dispatches_to_a_single_listener(): void
    {
        $received = null;

        $this->registry->listen(UserCreated::class, function (UserCreated $event) use (&$received): void {
            $received = $event;
        });

        $event = new UserCreated('Alice');
        $this->dispatcher->dispatch($event);

        $this->assertSame($event, $received);
    }

    #[Test]
    public function it_dispatches_to_multiple_listeners_in_order(): void
    {
        $log = [];

        $this->registry->listen(UserCreated::class, function () use (&$log): void { $log[] = 'first'; });
        $this->registry->listen(UserCreated::class, function () use (&$log): void { $log[] = 'second'; });
        $this->registry->listen(UserCreated::class, function () use (&$log): void { $log[] = 'third'; });

        $this->dispatcher->dispatch(new UserCreated('Alice'));

        $this->assertSame(['first', 'second', 'third'], $log);
    }

    #[Test]
    public function it_returns_the_same_event_instance(): void
    {
        $event  = new UserCreated('Alice');
        $result = $this->dispatcher->dispatch($event);

        $this->assertSame($event, $result);
    }

    #[Test]
    public function it_allows_listeners_to_mutate_event(): void
    {
        $this->registry->listen(UserCreated::class, function (UserCreated $event): void {
            $event->name = 'Modified';
        });

        $this->registry->listen(UserCreated::class, function (UserCreated $event) use (&$seen): void {
            $seen = $event->name;
        });

        $this->dispatcher->dispatch(new UserCreated('Original'));

        $this->assertSame('Modified', $seen);
    }

    // ── No listeners ──────────────────────────────────────────

    #[Test]
    public function it_returns_event_when_no_listeners_registered(): void
    {
        $event  = new UserCreated('Alice');
        $result = $this->dispatcher->dispatch($event);

        $this->assertSame($event, $result);
    }

    #[Test]
    public function it_does_not_error_with_dead_event_enabled(): void
    {
        $dispatcher = new EventDispatcher($this->registry, deadEvent: true);

        $event  = new UserCreated('Alice');
        $result = $dispatcher->dispatch($event);

        $this->assertSame($event, $result);
    }

    // ── Event routing ─────────────────────────────────────────

    #[Test]
    public function it_does_not_call_listeners_for_different_event(): void
    {
        $called = false;

        $this->registry->listen(UserDeleted::class, function () use (&$called): void {
            $called = true;
        });

        $this->dispatcher->dispatch(new UserCreated('Alice'));

        $this->assertFalse($called);
    }

    #[Test]
    public function it_dispatches_same_event_multiple_times(): void
    {
        $count = 0;

        $this->registry->listen(UserCreated::class, function () use (&$count): void {
            $count++;
        });

        $event = new UserCreated('Alice');
        $this->dispatcher->dispatch($event);
        $this->dispatcher->dispatch($event);

        $this->assertSame(2, $count);
    }

    #[Test]
    public function it_does_not_match_parent_class_listeners(): void
    {
        // Register a listener for the parent class
        $called = false;

        $this->registry->listen(BaseEvent::class, function () use (&$called): void {
            $called = true;
        });

        // Dispatch a child class instance — should NOT match because registry uses exact ::class
        $this->dispatcher->dispatch(new ExtendedEvent('Alice'));

        $this->assertFalse($called);
    }

    // ── Error propagation ─────────────────────────────────────

    #[Test]
    public function it_propagates_listener_exception(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('listener failed');

        $this->registry->listen(UserCreated::class, function (): void {
            throw new RuntimeException('listener failed');
        });

        $this->dispatcher->dispatch(new UserCreated('Alice'));
    }

    #[Test]
    public function it_short_circuits_on_listener_exception(): void
    {
        $secondCalled = false;

        $this->registry->listen(UserCreated::class, function (): void {
            throw new RuntimeException('fail');
        });

        $this->registry->listen(UserCreated::class, function () use (&$secondCalled): void {
            $secondCalled = true;
        });

        try {
            $this->dispatcher->dispatch(new UserCreated('Alice'));
        } catch (RuntimeException) {
            // expected
        }

        $this->assertFalse($secondCalled);
    }
}
