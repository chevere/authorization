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

use Chevere\Authorization\PermissionException;
use Chevere\Authorization\Role;
use Chevere\Authorization\Roles;
use Chevere\Authorization\RolesMask;
use Chevere\Tests\src\PostPermission;
use Chevere\Tests\src\UserPermission;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RolesMaskTest extends TestCase
{
    public function testInvokeBitmask(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            [bitmask]: Argument value provided `-1` is less than `0`
            PLAIN
        );
        (new RolesMask(new Roles()))->__invoke(-1);
    }

    public function testAssert(): void
    {
        $roles = new Roles(
            new Role(1, 'user', PostPermission::Create),
            new Role(2, 'admin', PostPermission::Create, UserPermission::Create, UserPermission::Ban),
            new Role(4, 'staff', UserPermission::Create)
        );
        $rolesMask = new RolesMask($roles);
        $this->assertSame($roles, $rolesMask->roles());
        $this->assertSame(
            [
                PostPermission::Create->value => 3,
                UserPermission::Create->value => 6,
                UserPermission::Ban->value => 2,
            ],
            iterator_to_array($rolesMask)
        );
        for ($i = 1; $i <= 7; $i++) {
            $rolesMask($i, ...$roles->forMask($i)->permissions());
            $this->assertTrue(
                $rolesMask->contains($i, ...$roles->forMask($i)->permissions())
            );
        }
    }

    #[DataProvider('dataProviderNotContains')]
    public function testNotContains(int $mask, array $arguments): void
    {
        $roles = new Roles(
            new Role(1, 'user', PostPermission::Create),
            new Role(2, 'admin', PostPermission::Create, UserPermission::Create, UserPermission::Ban),
            new Role(4, 'staff', UserPermission::Ban, UserPermission::Delete),
        );
        $rolesMask = new RolesMask($roles);
        $this->assertFalse(
            $rolesMask->contains($mask, ...$arguments)
        );
    }

    public static function dataProviderNotContains(): array
    {
        return [
            [
                3,
                [
                    UserPermission::Delete,
                ],
            ],
            [
                5,
                [
                    PostPermission::Create,
                    UserPermission::Create,
                ],
            ],
            [
                6,
                [
                    UserPermission::Create,
                    PostPermission::Delete,
                ],
            ],
            [
                7,
                [
                    UserPermission::Create,
                    PostPermission::Delete,
                ],
            ],
        ];
    }

    public function testPermissionNotGranted(): void
    {
        $this->expectException(PermissionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Permission `post.create` not granted
            Permission `user.create` not granted
            PLAIN
        );
        $roles = new Roles(new Role(1, 'user'));
        $rolesMask = new RolesMask($roles);
        $rolesMask->__invoke(1, PostPermission::Create, UserPermission::Create);
    }

    public function testUndefinedBits(): void
    {
        $this->expectException(PermissionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Bitmask `3` contains undefined role bits
            PLAIN
        );
        $roles = new Roles(
            new Role(1, 'user', PostPermission::Create),
            new Role(4, 'staff', UserPermission::Create)
        );
        $rolesMask = new RolesMask($roles);
        $this->assertFalse($rolesMask->contains(2, PostPermission::Create));
        $rolesMask->__invoke(3, PostPermission::Create);
    }

    public function testMissingGrants(): void
    {
        $this->expectException(PermissionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Permission `post.create` not granted (mask: `4`, required: `3`)
            Permission `user.ban` not granted
            PLAIN
        );
        $roles = new Roles(
            new Role(1, 'user', PostPermission::Create),
            new Role(2, 'admin', PostPermission::Create, UserPermission::Create),
            new Role(4, 'staff', UserPermission::Create),
        );
        $rolesMask = new RolesMask($roles);
        $rolesMask->__invoke(4, PostPermission::Create, UserPermission::Ban);
    }
}
