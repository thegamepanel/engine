<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Registries;

use Countable;
use Engine\Container\Exceptions\InvalidClassException;
use Engine\Events\Attributes\Listener;
use Engine\Events\Contracts\ListenerRegistry;
use Engine\Events\Exceptions\InvalidListenerException;
use Engine\Events\Registries\RuntimeListenerRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Events\Fixtures\ChildSubscriber;
use Tests\Unit\Events\Fixtures\NonStaticSubscriber;
use Tests\Unit\Events\Fixtures\StaticSubscriber;
use Tests\Unit\Events\Fixtures\UserCreated;
use Tests\Unit\Events\Fixtures\UserDeleted;

final class RuntimeListenerRegistryTest extends TestCase
{
    private RuntimeListenerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new RuntimeListenerRegistry();
        StaticSubscriber::reset();
    }

    // ── Contract ──────────────────────────────────────────────

    #[Test]
    public function it_implements_listener_registry(): void
    {
        $this->assertInstanceOf(ListenerRegistry::class, $this->registry);
    }

    // ── listen() + get() ──────────────────────────────────────

    #[Test]
    public function it_registers_and_retrieves_a_listener(): void
    {
        $listener = function (UserCreated $event): void {};

        $this->registry->listen(UserCreated::class, $listener);

        $this->assertCount(1, $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_registers_multiple_listeners_for_same_event(): void
    {
        $this->registry->listen(UserCreated::class, function (UserCreated $event): void {});
        $this->registry->listen(UserCreated::class, function (UserCreated $event): void {});

        $this->assertCount(2, $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_registers_listeners_for_different_events(): void
    {
        $this->registry->listen(UserCreated::class, function (UserCreated $event): void {});
        $this->registry->listen(UserDeleted::class, function (UserDeleted $event): void {});

        $this->assertCount(1, $this->registry->get(UserCreated::class));
        $this->assertCount(1, $this->registry->get(UserDeleted::class));
    }

    #[Test]
    public function it_preserves_listener_registration_order(): void
    {
        $log = [];

        $this->registry->listen(UserCreated::class, function () use (&$log): void { $log[] = 'first'; });
        $this->registry->listen(UserCreated::class, function () use (&$log): void { $log[] = 'second'; });
        $this->registry->listen(UserCreated::class, function () use (&$log): void { $log[] = 'third'; });

        foreach ($this->registry->get(UserCreated::class) as $listener) {
            $listener(new UserCreated('test'));
        }

        $this->assertSame(['first', 'second', 'third'], $log);
    }

    #[Test]
    public function it_returns_empty_array_for_unregistered_event(): void
    {
        $this->assertSame([], $this->registry->get(UserCreated::class));
    }

    // ── subscribe() — object instance ─────────────────────────

    #[Test]
    public function it_discovers_listener_methods_on_object(): void
    {
        $subscriber = new class {
            public bool $called = false;

            #[Listener]
            public function handle(UserCreated $event): void
            {
                $this->called = true;
            }
        };

        $this->registry->subscribe($subscriber);

        $listeners = $this->registry->get(UserCreated::class);
        $this->assertCount(1, $listeners);

        // Invoke to verify it works
        $listeners[0](new UserCreated('test'));
        $this->assertTrue($subscriber->called);
    }

    #[Test]
    public function it_ignores_methods_without_listener_attribute(): void
    {
        $subscriber = new class {
            #[Listener]
            public function withAttribute(UserCreated $event): void {}

            public function withoutAttribute(UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertCount(1, $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_discovers_multiple_listener_methods(): void
    {
        $subscriber = new class {
            #[Listener]
            public function first(UserCreated $event): void {}

            #[Listener]
            public function second(UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertCount(2, $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_registers_different_event_types_from_subscriber(): void
    {
        $subscriber = new class {
            #[Listener]
            public function onCreated(UserCreated $event): void {}

            #[Listener]
            public function onDeleted(UserDeleted $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertCount(1, $this->registry->get(UserCreated::class));
        $this->assertCount(1, $this->registry->get(UserDeleted::class));
    }

    #[Test]
    public function it_registers_two_listeners_for_same_event_from_subscriber(): void
    {
        $subscriber = new class {
            #[Listener]
            public function first(UserCreated $event): void {}

            #[Listener]
            public function second(UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertCount(2, $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_binds_instance_method_to_correct_object(): void
    {
        $subscriber = new class {
            public array $handled = [];

            #[Listener]
            public function handle(UserCreated $event): void
            {
                $this->handled[] = $event;
            }
        };

        $this->registry->subscribe($subscriber);

        $event = new UserCreated('Alice');
        $this->registry->get(UserCreated::class)[0]($event);

        $this->assertCount(1, $subscriber->handled);
        $this->assertSame($event, $subscriber->handled[0]);
    }

    #[Test]
    public function it_handles_static_method_on_object_subscriber(): void
    {
        $subscriber = new StaticSubscriber();

        $this->registry->subscribe($subscriber);

        $event = new UserCreated('Alice');
        $this->registry->get(UserCreated::class)[0]($event);

        $this->assertCount(1, StaticSubscriber::$handled);
        $this->assertSame($event, StaticSubscriber::$handled[0]);
    }

    #[Test]
    public function it_discovers_inherited_listener_methods(): void
    {
        $subscriber = new ChildSubscriber();

        $this->registry->subscribe($subscriber);

        $listeners = $this->registry->get(UserCreated::class);
        $this->assertCount(1, $listeners);

        // Verify the inherited method is bound to the child instance
        $event = new UserCreated('Alice');
        $listeners[0]($event);

        $this->assertCount(1, $subscriber->handled);
        $this->assertSame($event, $subscriber->handled[0]);
    }

    #[Test]
    public function it_handles_nullable_type_parameter(): void
    {
        $subscriber = new class {
            #[Listener]
            public function handle(?UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertCount(1, $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_handles_interface_typed_parameter(): void
    {
        $subscriber = new class {
            #[Listener]
            public function handle(Countable $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertCount(1, $this->registry->get(Countable::class));
    }

    // ── subscribe() — class string ────────────────────────────

    #[Test]
    public function it_discovers_static_listeners_from_class_string(): void
    {
        $this->registry->subscribe(StaticSubscriber::class);

        $this->assertCount(1, $this->registry->get(UserCreated::class));
        $this->assertCount(1, $this->registry->get(UserDeleted::class));
    }

    #[Test]
    public function it_invokes_static_listener_from_class_string(): void
    {
        $this->registry->subscribe(StaticSubscriber::class);

        $event = new UserCreated('Alice');
        $this->registry->get(UserCreated::class)[0]($event);

        $this->assertCount(1, StaticSubscriber::$handled);
        $this->assertSame($event, StaticSubscriber::$handled[0]);
    }

    // ── subscribe() — validation errors ───────────────────────

    #[Test]
    public function it_throws_on_non_static_method_with_class_string(): void
    {
        $this->expectException(InvalidListenerException::class);

        $this->registry->subscribe(NonStaticSubscriber::class);
    }

    #[Test]
    public function it_throws_on_zero_parameter_listener(): void
    {
        $this->expectException(InvalidListenerException::class);

        $subscriber = new class {
            #[Listener]
            public function handle(): void {}
        };

        $this->registry->subscribe($subscriber);
    }

    #[Test]
    public function it_throws_on_two_parameter_listener(): void
    {
        $this->expectException(InvalidListenerException::class);

        $subscriber = new class {
            #[Listener]
            public function handle(UserCreated $event, string $extra): void {}
        };

        $this->registry->subscribe($subscriber);
    }

    #[Test]
    public function it_throws_on_builtin_type_parameter(): void
    {
        $this->expectException(InvalidListenerException::class);

        $subscriber = new class {
            #[Listener]
            public function handle(string $event): void {}
        };

        $this->registry->subscribe($subscriber);
    }

    #[Test]
    public function it_throws_on_no_type_hint(): void
    {
        $this->expectException(InvalidListenerException::class);

        $subscriber = new class {
            #[Listener]
            public function handle($event): void {}
        };

        $this->registry->subscribe($subscriber);
    }

    #[Test]
    public function it_throws_on_union_type_parameter(): void
    {
        $this->expectException(InvalidListenerException::class);

        $subscriber = new class {
            #[Listener]
            public function handle(UserCreated|UserDeleted $event): void {}
        };

        $this->registry->subscribe($subscriber);
    }

    #[Test]
    public function it_throws_on_intersection_type_parameter(): void
    {
        $this->expectException(InvalidListenerException::class);

        $subscriber = new class {
            #[Listener]
            public function handle(Countable&\Iterator $event): void {}
        };

        $this->registry->subscribe($subscriber);
    }

    // ── subscribe() — edge cases ──────────────────────────────

    #[Test]
    public function it_registers_nothing_for_subscriber_with_no_listener_methods(): void
    {
        $subscriber = new class {
            public function notAListener(UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertSame([], $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_ignores_protected_listener_methods(): void
    {
        $subscriber = new class {
            #[Listener]
            protected function handle(UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertSame([], $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_ignores_private_listener_methods(): void
    {
        $subscriber = new class {
            #[Listener]
            private function handle(UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);

        $this->assertSame([], $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_duplicates_listeners_when_subscribing_same_object_twice(): void
    {
        $subscriber = new class {
            #[Listener]
            public function handle(UserCreated $event): void {}
        };

        $this->registry->subscribe($subscriber);
        $this->registry->subscribe($subscriber);

        $this->assertCount(2, $this->registry->get(UserCreated::class));
    }

    #[Test]
    public function it_throws_on_invalid_class_string(): void
    {
        $this->expectException(InvalidClassException::class);

        $this->registry->subscribe('NonExistent\\ClassName');
    }
}
