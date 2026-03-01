<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Fixtures;

use Engine\Events\Attributes\Listener;

final class NonStaticSubscriber
{
    #[Listener]
    public function onUserCreated(UserCreated $event): void
    {
    }
}
