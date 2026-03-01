<?php

namespace Engine\Entities\Contracts;

interface IsSoftDeletable
{
    /**
     * Check if the entity has been deleted.
     *
     * @return bool
     */
    public function hasBeenDeleted(): bool;

    /**
     * Mark the entity as deleted.
     *
     * @return void
     */
    public function markDeleted(): void;
}
