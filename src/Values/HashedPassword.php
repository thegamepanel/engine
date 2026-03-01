<?php
declare(strict_types=1);

namespace Engine\Values;

use SensitiveParameter;

final readonly class HashedPassword
{
    public static function make(#[SensitiveParameter] string $password): self
    {
        return new self(password_hash($password, PASSWORD_ARGON2ID));
    }

    public string $hash;

    public function __construct(#[SensitiveParameter] string $hash)
    {
        $this->hash = $hash;
    }

    public function verify(#[SensitiveParameter] string $password): bool
    {
        return password_verify($password, $this->hash);
    }
}
