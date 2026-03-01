<?php
declare(strict_types=1);

namespace Engine\Auth;

use Engine\Entities\Contracts\Entity;
use Engine\Entities\Contracts\HasTimestamps;
use Engine\Entities\Contracts\IsSoftDeletable;
use Engine\Entities\EntityId;
use Engine\Values\HashedPassword;
use Engine\Values\Timestamps;
use SensitiveParameter;

/**
 * User entity.
 *
 * @implements \Engine\Entities\Contracts\Entity<\Engine\Auth\UserId>
 */
final class User implements Entity, HasTimestamps, IsSoftDeletable
{
    public function make(
        string                       $email,
        #[SensitiveParameter] string $password,
        bool                         $active = true,
    ): self
    {
        return new self(
            UserId::make(),
            $email,
            HashedPassword::make($password),
            $active,
            new Timestamps()
        );
    }

    public UserId $id;

    private(set) string $email;

    private(set) HashedPassword $password;

    private(set) bool $active = true;

    private(set) Timestamps $timestamps;

    public function __construct(
        UserId         $id,
        string         $email,
        HashedPassword $password,
        bool           $active,
        Timestamps     $timestamps,
    )
    {
        $this->id         = $id;
        $this->email      = $email;
        $this->password   = $password;
        $this->active     = $active;
        $this->timestamps = $timestamps;
    }

    /**
     * Get the entity's ID.
     *
     * @return \Engine\Auth\UserId
     */
    public function getId(): EntityId
    {
        return $this->id;
    }

    public function activate(): self
    {
        $this->active = true;

        return $this;
    }

    public function deactivate(): self
    {
        $this->active = false;

        return $this;
    }

    public function changePassword(#[SensitiveParameter] string $password): self
    {
        $this->password = HashedPassword::make($password);

        return $this;
    }

    /**
     * Get timestamps.
     *
     * @return \Engine\Values\Timestamps
     */
    public function getTimestamps(): Timestamps
    {
        return $this->timestamps;
    }

    /**
     * Check if the entity has been deleted.
     *
     * @return bool
     */
    public function hasBeenDeleted(): bool
    {
        return $this->getTimestamps()->get('deleted_at') !== null;
    }
}
