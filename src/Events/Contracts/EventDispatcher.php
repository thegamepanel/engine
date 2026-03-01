<?php

namespace Engine\Events\Contracts;

interface EventDispatcher
{
    /**
     * Dispatch an event.
     *
     * @template TEvent of object
     *
     * @param TEvent $event
     *
     * @return TEvent
     */
    public function dispatch(object $event): object;
}
