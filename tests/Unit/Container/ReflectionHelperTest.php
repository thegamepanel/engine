<?php
declare(strict_types=1);

namespace Tests\Unit\Container;

use Engine\Container\Attributes\Lazy;
use Engine\Container\Contracts\Resolvable;
use Engine\Container\Exceptions\InvalidClassException;
use Engine\Container\ReflectionHelper;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tests\Unit\Container\Fixtures\ClassWithMethods;
use Tests\Unit\Container\Fixtures\LazyClass;

#[Group('unit'), Group('container'), Group('reflection-helper')]
class ReflectionHelperTest extends TestCase
{
    /**
     * - A valid class name returns a ReflectionClass instance for exactly that class.
     */
    #[Test]
    public function getClassReflectorReturnsReflectionClassForValidClass(): void
    {
        $reflector = ReflectionHelper::getClassReflector(ClassWithMethods::class);

        $this->assertInstanceOf(ReflectionClass::class, $reflector);
        $this->assertSame(ClassWithMethods::class, $reflector->getName());
    }

    /**
     * - A class name that does not exist throws an InvalidClassException rather than
     *   surfacing a raw ReflectionException to the caller.
     */
    #[Test]
    public function getClassReflectorThrowsForNonExistentClass(): void
    {
        $this->expectException(InvalidClassException::class);

        ReflectionHelper::getClassReflector('NonExistentClass');
    }

    /**
     * - The default `$instanceOf = false` performs an exact class match, so looking
     *   up a parent interface returns null even when an implementing attribute is present.
     */
    #[Test]
    public function getAttributeInstanceReturnsNullForParentTypeWithDefaultExactMatch(): void
    {
        // LazyClass has #[Lazy], and Lazy implements Resolvable. With exact matching
        // (the default), searching for Resolvable::class finds nothing.
        $reflector = new ReflectionClass(LazyClass::class);

        $result = ReflectionHelper::getAttributeInstance($reflector, Resolvable::class);

        $this->assertNull($result);
    }

    /**
     * - Passing `$instanceOf = true` uses `IS_INSTANCEOF` flag matching, so a search
     *   for a parent interface finds attributes whose class implements that interface.
     */
    #[Test]
    public function getAttributeInstanceWithInstanceOfFindsDerivedAttributeViaParentType(): void
    {
        // LazyClass has #[Lazy]. Lazy implements Resolvable. With $instanceOf = true,
        // searching for Resolvable::class returns the Lazy attribute instance.
        $reflector = new ReflectionClass(LazyClass::class);

        $result = ReflectionHelper::getAttributeInstance($reflector, Resolvable::class, true);

        $this->assertInstanceOf(Lazy::class, $result);
    }

    /**
     * - When a class has a single attribute, the first (index 0) element is returned,
     *   confirming the correct array index is used.
     */
    #[Test]
    public function getAttributeInstanceReturnsFirstAttributeForClassWithSingleAttribute(): void
    {
        // LazyClass has exactly one attribute (#[Lazy]). Index [0] returns it;
        // index [1] would return null.
        $reflector = new ReflectionClass(LazyClass::class);

        $result = ReflectionHelper::getAttributeInstance($reflector, Lazy::class);

        $this->assertInstanceOf(Lazy::class, $result);
    }
}
