<?php

namespace Engine\Events\Contracts;

interface ListenerRegistry
{
    /**
     * Register an event listener.
     *
     * @template TEvent of object
     *
     * @param class-string<TEvent>   $event
     * @param callable(TEvent): void $listener
     *
     * @return void
     */
    public function listen(string $event, callable $listener): void;

    /**
     * Register an event subscriber.
     *
     * @param class-string|object $class
     *
     * @return void
     */
    public function subscribe(string|object $class): void;

    /**
     * Get all of the listeners for a given event.
     *
     * @template TEvent of object
     *
     * @param class-string<TEvent> $event
     *
     * @return array<callable(TEvent): void>
     */
    public function get(string $event): array;
}
