<?php
declare(strict_types=1);

namespace Engine\Events;

use Engine\Events\Contracts\ListenerRegistry;

final readonly class EventDispatcher implements Contracts\EventDispatcher
{
    public function __construct(
        private ListenerRegistry $registry,
        private bool             $deadEvent = false,
    )
    {
    }

    /**
     * Dispatch a dead event for an event with no listeners.
     *
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @return void
     */
    private function deadEvent(object $event): void
    {
        if ($this->deadEvent === false) {
            return;
        }

        // TODO: Implement dead event.
    }

    /**
     * Dispatch an event.
     *
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @return TEvent
     */
    public function dispatch(object $event): object
    {
        $listeners = $this->registry->get($event::class);

        if (empty($listeners)) {
            $this->deadEvent($event);
        } else {
            foreach ($listeners as $listener) {
                $listener($event);
            }
        }

        return $event;
    }
}
