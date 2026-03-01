<?php

namespace Engine\Entities\Contracts;

use Engine\Entities\EntityId;

/**
 * @template TId of \Engine\Entities\EntityId
 */
interface Entity
{
    /**
     * Get the entity's ID.
     *
     * @return TId
     */
    public function getId(): EntityId;
}
