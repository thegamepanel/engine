<?php
declare(strict_types=1);

namespace Engine\Events\Registries;

use Engine\Container\Concerns\HelpsWithReflection;
use Engine\Events\Attributes\Listener;
use Engine\Events\Contracts\ListenerRegistry;
use Engine\Events\Exceptions\InvalidListenerException;
use ReflectionMethod;
use ReflectionNamedType;

final class RuntimeListenerRegistry implements ListenerRegistry
{
    use HelpsWithReflection;

    /**
     * @var array<class-string, non-empty-array<callable(object): void>>
     */
    private array $listeners = [];

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
    public function listen(string $event, callable $listener): void
    {
        /** @var callable(object): void $listener */
        $this->listeners[$event][] = $listener;
    }

    /**
     * Register an event subscriber.
     *
     * @param class-string|object $class
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function subscribe(string|object $class): void
    {
        // Get all the listeners defined in the subscriber.
        $listeners = $this->findListenersInSubscriber($class);

        // Then loop through and register them.
        foreach ($listeners as $event => $eventListeners) {
            foreach ($eventListeners as $listener) {
                $this->listen($event, $listener);
            }
        }
    }

    /**
     * Get all the listeners for a given event.
     *
     * @template TEvent of object
     *
     * @param class-string<TEvent> $event
     *
     * @return array<callable(TEvent): void>
     */
    public function get(string $event): array
    {
        return $this->listeners[$event] ?? [];
    }

    /**
     * @param class-string|object $class
     *
     * @return array<class-string, array<callable(object): void>>
     *
     * @throws \ReflectionException
     */
    private function findListenersInSubscriber(string|object $class): array
    {
        $isObject  = is_object($class);
        $reflector = $this->getClassReflector($class);
        $methods   = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        $listeners = [];

        foreach ($methods as $method) {
            if (empty($method->getAttributes(Listener::class))) {
                continue;
            }

            if ($method->getNumberOfParameters() !== 1) {
                throw InvalidListenerException::wrongParameterCount($reflector->getName(), $method->getName());
            }

            $type = $method->getParameters()[0]->getType();

            if (
                ! $type instanceof ReflectionNamedType
                || $type->isBuiltin()
                || (! class_exists($type->getName()) && ! interface_exists($type->getName()))
            ) {
                throw InvalidListenerException::invalidEventClass($reflector->getName(), $method->getName());
            }

            if ($isObject === false && $method->isStatic() === false) {
                throw InvalidListenerException::notStatic($reflector->getName(), $method->getName());
            }

            if ($method->isStatic()) {
                $listeners[$type->getName()][] = $method->getClosure();
            } else {
                /**
                 * If we're here, the <code>$class</code> is an object;
                 * otherwise the above exception would have caught it.
                 *
                 * @var object $class
                 */
                $listeners[$type->getName()][] = $method->getClosure($class);
            }
        }

        return $listeners;
    }
}
