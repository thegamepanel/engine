<?php
declare(strict_types=1);

namespace Engine\Entities;

use DateTimeImmutable;
use Engine\Database\Connection;
use Engine\Database\Query\Delete;
use Engine\Database\Query\Insert;
use Engine\Database\Query\Select;
use Engine\Database\Query\Update;
use Engine\Database\Row;
use Engine\Database\WriteResult;
use Engine\Entities\Contracts\Entity;
use Engine\Entities\Contracts\EntityStore;
use Engine\Entities\Contracts\HasTimestamps;
use Engine\Entities\Contracts\IsSoftDeletable;
use Engine\Entities\EntityId as TEntityId;

/**
 * @template TEntityId of \Engine\Entities\EntityId
 * @template TEntity of \Engine\Entities\Contracts\Entity<TEntityId>
 *
 * @implements \Engine\Entities\Contracts\EntityStore<TEntityId, TEntity>
 */
abstract class BaseEntityStore implements EntityStore
{
    /**
     * @var \Engine\Database\Connection
     */
    private Connection $connection;

    /**
     * @var \Engine\Entities\EntityMap
     */
    private EntityMap $map;

    /**
     * @var class-string<TEntity>
     */
    private string $entityClass;

    private string $table;

    /**
     * @param \Engine\Database\Connection $connection
     * @param \Engine\Entities\EntityMap  $map
     * @param class-string<TEntity>       $entityClass
     * @param string                      $table
     */
    public function __construct(
        Connection $connection,
        EntityMap  $map,
        string     $entityClass,
        string     $table,
    )
    {
        $this->connection  = $connection;
        $this->map         = $map;
        $this->entityClass = $entityClass;
        $this->table       = $table;
    }

    /**
     * Hydrate an entity from a database row.
     *
     * @param \Engine\Database\Row $row
     *
     * @return TEntity
     */
    abstract protected function hydrate(Row $row): Entity;

    /**
     * Dehydrate an entity into a storable away.
     *
     * @param TEntity $entity
     *
     * @return array<string, mixed>
     */
    abstract protected function dehydrate(Entity $entity): array;

    /**
     * Find an entity by its ID.
     *
     * @param TEntityId $id
     *
     * @return TEntity|null
     */
    public function find(EntityId $id): ?Entity
    {
        if ($this->map->has($this->entityClass, $id->id)) {
            return $this->map->get($this->entityClass, $id->id);
        }

        $query = Select::from($this->table)->where('id', $id->id)->limit(1);

        if (is_subclass_of($this->entityClass, IsSoftDeletable::class)) {
            $query->whereNull('deleted_at');
        }

        $result = $this->connection->query($query);

        if ($result->isEmpty()) {
            return null;
        }

        /** @var \Engine\Database\Row $row */
        $row    = $result->first();
        $entity = $this->hydrate($row);

        $this->map->add($entity, $row->toArray());

        return $entity;
    }

    /**
     * Save an entity.
     *
     * @param TEntity $entity
     *
     * @return bool
     */
    public function save(Entity $entity): bool
    {
        if ($this->map->has($this->entityClass, $entity->getId()->id)) {
            $result = $this->update($entity);
        } else {
            $result = $this->create($entity);
        }

        if ($result && $result->affectedRows() !== 0) {
            $this->map->add($entity, $this->dehydrate($entity));

            return true;
        }

        return false;
    }

    /**
     * @param TEntity $entity
     *
     * @return \Engine\Database\WriteResult|null
     */
    private function update(Entity $entity): ?WriteResult
    {
        // If the entity has timestamps, set the updated_at timestamp.
        if ($entity instanceof HasTimestamps) {
            $entity->getTimestamps()->set('updated_at', new DateTimeImmutable());
        }

        $data    = $this->dehydrate($entity);
        $changes = $this->map->changes($this->entityClass, $entity->getId()->id, $data);

        if (empty($changes)) {
            return null;
        }

        return $this->connection->execute(
            Update::table($this->table)->set($changes)->where('id', $entity->getId()->id)
        );
    }

    /**
     * @param TEntity $entity
     *
     * @return \Engine\Database\WriteResult
     */
    private function create(Entity $entity): WriteResult
    {
        // If the entity has timestamps, set the created_at, and updated_at
        // timestamps.
        if ($entity instanceof HasTimestamps) {
            $entity->getTimestamps()->set('created_at', new DateTimeImmutable());
            $entity->getTimestamps()->set('updated_at', new DateTimeImmutable());
        }

        $data = $this->dehydrate($entity);

        return $this->connection->execute(
            Insert::into($this->table)->values($data)
        );
    }

    /**
     * Delete an entity.
     *
     * @param TEntity $entity
     *
     * @return bool
     */
    public function delete(Entity $entity): bool
    {
        if ($entity instanceof IsSoftDeletable) {
            $entity->markDeleted();

            $result = $this->update($entity);
        } else {
            $result = $this->connection->execute(
                Delete::from($this->table)->where('id', $entity->getId()->id)
            );
        }

        if ($result && $result->affectedRows() !== 0) {
            $this->map->forget($this->entityClass, $entity->getId()->id);

            return true;
        }

        return false;
    }
}
