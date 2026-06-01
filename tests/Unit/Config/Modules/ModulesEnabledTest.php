<?php
declare(strict_types=1);

namespace Tests\Unit\Config\Modules;

use Engine\Config\Modules\ModulesEnabled;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit'), Group('config'), Group('modules')]
class ModulesEnabledTest extends TestCase
{
    /**
     * - fromArray() with a list of strings constructs with the expected modules.
     */
    #[Test]
    public function fromArrayWithStringListConstructs(): void
    {
        $modules = ModulesEnabled::fromArray(['admin', 'billing']);

        $this->assertSame(['admin', 'billing'], $modules->modules);
    }

    /**
     * - fromArray() with an empty array constructs with no modules.
     */
    #[Test]
    public function fromArrayWithEmptyArrayConstructs(): void
    {
        $modules = ModulesEnabled::fromArray([]);

        $this->assertSame([], $modules->modules);
    }

    /**
     * - fromArray() with non-string elements throws.
     */
    #[Test]
    public function fromArrayWithNonStringElementsThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ModulesEnabled::fromArray([1, 2]);
    }

    /**
     * - has() returns true when the module is in the list.
     */
    #[Test]
    public function hasReturnsTrueWhenPresent(): void
    {
        $modules = ModulesEnabled::fromArray(['admin', 'billing']);

        $this->assertTrue($modules->has('admin'));
    }

    /**
     * - has() returns false when the module is absent.
     */
    #[Test]
    public function hasReturnsFalseWhenAbsent(): void
    {
        $modules = ModulesEnabled::fromArray(['admin']);

        $this->assertFalse($modules->has('missing'));
    }

    /**
     * - fromArray() reindexes associative input to a list.
     *
     * Kills the UnwrapArrayValues mutant that would skip array_values().
     */
    #[Test]
    public function fromArrayReindexesAssociativeInput(): void
    {
        $modules = ModulesEnabled::fromArray([
            'first'  => 'admin',
            'second' => 'billing',
        ]);

        // The result must be a 0-indexed list, not preserve the string keys.
        $this->assertSame(['admin', 'billing'], $modules->modules);
        $this->assertArrayHasKey(0, $modules->modules);
        $this->assertArrayHasKey(1, $modules->modules);
        $this->assertArrayNotHasKey('first', $modules->modules);
    }
}
