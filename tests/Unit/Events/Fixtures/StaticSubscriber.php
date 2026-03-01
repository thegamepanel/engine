<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Fixtures;

use Engine\Events\Attributes\Listener;

final class StaticSubscriber
{
    /** @var list<object> */
    public static array $handled = [];

    public static function reset(): void
    {
        self::$handled = [];
    }

    #[Listener]
    public static function onUserCreated(UserCreated $event): void
    {
        self::$handled[] = $event;
    }

    #[Listener]
    public static function onUserDeleted(UserDeleted $event): void
    {
        self::$handled[] = $event;
    }

    public static function notAListener(UserCreated $event): void
    {
        // No #[Listener] attribute — should be ignored
    }
}
