<?php

/*
 * This file is part of Chevere.
 *
 * (c) Rodolfo Berrios <rodolfo@chevere.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Tests;

use Chevere\Authorization\Permissions;
use Chevere\Tests\src\AppPermission;
use Chevere\Tests\src\UserPermission;
use LogicException;
use PHPUnit\Framework\TestCase;

final class PermissionsTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Missing permission(s): app.create, app.delete
            PLAIN
        );
        $permissions = new Permissions();
        $this->assertSame([], iterator_to_array($permissions));
        $permissions->contains();
        $permissions->assert();
        $this->assertFalse(
            $permissions->contains(AppPermission::Create)
        );
        $permissions->assert(
            AppPermission::Create,
            AppPermission::Delete
        );
    }

    public function testPermissions(): void
    {
        $arguments = [
            AppPermission::Create,
            UserPermission::Create,
        ];
        $permissions = new Permissions(...$arguments);
        $permissions->assert(...$arguments);
        $this->assertSame(
            $arguments,
            iterator_to_array($permissions)
        );
        $this->assertTrue(
            $permissions->contains(...$arguments)
        );
        $this->assertFalse(
            $permissions->contains(AppPermission::Delete)
        );
        $this->assertFalse(
            $permissions->contains(UserPermission::Create, AppPermission::Delete)
        );
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Missing permission(s): app.delete
            PLAIN
        );
        $permissions->assert(UserPermission::Create, AppPermission::Delete);
    }

    public function testWith(): void
    {
        $arguments = [
            AppPermission::Create,
            AppPermission::Delete,
            UserPermission::Create,
        ];
        $foo = new Permissions(
            AppPermission::Create,
        );
        $bar = new Permissions(
            AppPermission::Delete,
        );
        $fooWith = $foo->withMerge($bar);
        $this->assertNotSame($foo, $fooWith);
        $this->assertCount(2, $fooWith);
        $permissions = new Permissions(...$arguments);
        $fooWith = $fooWith->withMerge($permissions);
        $this->assertCount(3, $fooWith);
        $this->assertSame(
            $arguments,
            iterator_to_array($fooWith)
        );
    }
}
