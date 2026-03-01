<?php
declare(strict_types=1);

namespace Tests\Unit\Values;

use Engine\Values\HashedPassword;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionParameter;
use SensitiveParameter;

final class HashedPasswordTest extends TestCase
{
    // ── make() ────────────────────────────────────────────────

    #[Test]
    public function make_returns_hashed_password_instance(): void
    {
        $password = HashedPassword::make('secret');

        $this->assertInstanceOf(HashedPassword::class, $password);
    }

    #[Test]
    public function make_hash_is_not_the_original_password(): void
    {
        $password = HashedPassword::make('secret');

        $this->assertNotSame('secret', $password->hash);
    }

    #[Test]
    public function make_uses_argon2id_algorithm(): void
    {
        $password = HashedPassword::make('secret');

        $this->assertStringStartsWith('$argon2id$', $password->hash);
    }

    #[Test]
    public function make_produces_different_hashes_for_same_password(): void
    {
        $first  = HashedPassword::make('secret');
        $second = HashedPassword::make('secret');

        $this->assertNotSame($first->hash, $second->hash);
    }

    // ── Constructor ───────────────────────────────────────────

    #[Test]
    public function constructor_stores_hash(): void
    {
        $hash     = '$argon2id$v=19$m=65536,t=4,p=1$precomputed';
        $password = new HashedPassword($hash);

        $this->assertSame($hash, $password->hash);
    }

    // ── verify() ──────────────────────────────────────────────

    #[Test]
    public function verify_returns_true_for_correct_password(): void
    {
        $password = HashedPassword::make('secret');

        $this->assertTrue($password->verify('secret'));
    }

    #[Test]
    public function verify_returns_false_for_incorrect_password(): void
    {
        $password = HashedPassword::make('secret');

        $this->assertFalse($password->verify('wrong'));
    }

    // ── SensitiveParameter ────────────────────────────────────

    #[Test]
    public function all_password_params_are_marked_sensitive(): void
    {
        $methods = [
            ['__construct', 'hash'],
            ['make', 'password'],
            ['verify', 'password'],
        ];

        foreach ($methods as [$method, $param]) {
            $ref   = new ReflectionMethod(HashedPassword::class, $method);
            $attrs = $this->getSensitiveAttribute($ref, $param);

            $this->assertNotEmpty(
                $attrs,
                sprintf('%s::%s() parameter $%s should have #[SensitiveParameter]', HashedPassword::class, $method, $param),
            );
        }
    }

    /**
     * @return array<\ReflectionAttribute<SensitiveParameter>>
     */
    private function getSensitiveAttribute(ReflectionMethod $method, string $paramName): array
    {
        foreach ($method->getParameters() as $param) {
            if ($param->getName() === $paramName) {
                return $param->getAttributes(SensitiveParameter::class);
            }
        }

        return [];
    }
}
