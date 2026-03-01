<?php
declare(strict_types=1);

namespace Tests\Unit\Events\Fixtures;

use Engine\Events\Attributes\Listener;

class ParentSubscriber
{
    /** @var list<object> */
    public array $handled = [];

    #[Listener]
    public function onUserCreated(UserCreated $event): void
    {
        $this->handled[] = $event;
    }
}
