<?php
declare(strict_types=1);

namespace Engine\Server;

use Engine\Entities\Contracts\Entity;
use Engine\Entities\EntityId;

/**
 * User entity.
 *
 * @implements \Engine\Entities\Contracts\Entity<\Engine\Server\ServerId>
 */
final class Server implements Entity
{
    public ServerId $id;

    public function __construct(
        ServerId $id
    )
    {
        $this->id = $id;
    }

    /**
     * Get the entity's ID.
     *
     * @return \Engine\Server\ServerId
     */
    public function getId(): EntityId
    {
        return $this->id;
    }
}
