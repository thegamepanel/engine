<?php
declare(strict_types=1);

namespace Engine\Container;

use Engine\Container\Contracts\Qualifier;
use Engine\Container\Contracts\Resolvable;

/**
 * @template TClass of object
 */
final class Resolution
{
    /**
     * @param class-string<TClass> $class
     *
     * @return static
     */
    public static function for(string $class): self
    {
        return new self($class);
    }

    /**
     * @var class-string<TClass>
     */
    public readonly string $class;

    /**
     * @var array<string, mixed>
     */
    private(set) array $arguments = [];

    /**
     * @var string|null
     */
    private(set) ?string $name = null;

    /**
     * @var \Engine\Container\Contracts\Qualifier|null
     */
    private(set) ?Qualifier $qualifier = null;

    /**
     * @var \Engine\Container\Contracts\Resolvable|null
     */
    private(set) ?Resolvable $resolvable = null;

    /**
     * @var bool
     */
    private(set) bool $lazily = false;

    /**
     * @var bool
     */
    private(set) bool $liminal = false;

    /**
     * @param class-string<TClass> $class
     */
    private function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @param array<string, mixed> $arguments
     *
     * @return static
     */
    public function with(array $arguments): self
    {
        $this->arguments = array_merge($this->arguments, $arguments);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function named(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param \Engine\Container\Contracts\Qualifier $qualifier
     *
     * @return static
     */
    public function qualifiedBy(Qualifier $qualifier): self
    {
        $this->qualifier = $qualifier;

        return $this;
    }

    /**
     * @param \Engine\Container\Contracts\Resolvable $resolvable
     *
     * @return static
     */
    public function resolveWith(Resolvable $resolvable): self
    {
        $this->resolvable = $resolvable;

        return $this;
    }

    /**
     * @return static
     */
    public function lazily(): self
    {
        $this->lazily = true;

        return $this;
    }

    /**
     * @return static
     */
    public function liminal(): self
    {
        $this->liminal = true;

        return $this;
    }

    /**
     * @return bool
     *
     * @phpstan-assert-if-true true $this->lazily
     * @phpstan-assert-if-false false $this->lazily
     */
    public function shouldResolveLazily(): bool
    {
        return $this->lazily;
    }

    /**
     * @return bool
     *
     * @phpstan-assert-if-true !null $this->resolvable
     * @phpstan-assert-if-false null $this->resolvable
     */
    public function usesCustomResolver(): bool
    {
        return $this->resolvable !== null;
    }

    /**
     * @return bool
     *
     * @phpstan-assert-if-true string $this->name
     * @phpstan-assert-if-false null $this->name
     */
    public function isNamed(): bool
    {
        return $this->name !== null;
    }

    /**
     * @return bool
     *
     * @phpstan-assert-if-true true $this->liminal
     * @phpstan-assert-if-false false $this->liminal
     */
    public function isLiminal(): bool
    {
        return $this->liminal;
    }

    /**
     * @return bool
     *
     * @phpstan-assert-if-true !null $this->qualifier
     * @phpstan-assert-if-false null $this->qualifier
     */
    public function isQualified(): bool
    {
        return $this->qualifier !== null;
    }
}
