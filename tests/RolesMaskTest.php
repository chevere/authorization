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
use Chevere\Tests\src\AppPermission;
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
            [bitmask]: Argument value provided `0` is less than `1`
            PLAIN
        );
        (new RolesMask(new Roles()))->__invoke(0);
    }

    public function testAssert(): void
    {
        $roles = new Roles(
            new Role(1, 'user', AppPermission::Create),
            new Role(2, 'admin', AppPermission::Create, UserPermission::Create, UserPermission::Ban),
            new Role(4, 'staff', UserPermission::Create)
        );
        $rolesMask = new RolesMask($roles);
        $this->assertSame($roles, $rolesMask->roles());
        $this->assertSame(
            [
                AppPermission::Create->value => 3,
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
            new Role(1, 'user', AppPermission::Create),
            new Role(2, 'admin', AppPermission::Create, UserPermission::Create, UserPermission::Ban),
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
                    AppPermission::Create,
                    UserPermission::Create,
                ],
            ],
            [
                6,
                [
                    UserPermission::Create,
                    AppPermission::Delete,
                ],
            ],
            [
                7,
                [
                    UserPermission::Create,
                    AppPermission::Delete,
                ],
            ],
        ];
    }

    public function testPermissionNotGranted(): void
    {
        $this->expectException(PermissionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Permission "app.create" not granted
            Permission "user.create" not granted
            PLAIN
        );
        $roles = new Roles(new Role(1, 'user'));
        $rolesMask = new RolesMask($roles);
        $rolesMask->__invoke(1, AppPermission::Create, UserPermission::Create);
    }

    public function testInvalidMask(): void
    {
        $this->expectException(PermissionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Bitmask "3" contains undefined role bits
            PLAIN
        );
        $roles = new Roles(
            new Role(1, 'user', AppPermission::Create),
            new Role(4, 'staff', UserPermission::Create)
        );
        $rolesMask = new RolesMask($roles);
        $rolesMask->__invoke(3, AppPermission::Create);
    }

    public function testMissingGrants(): void
    {
        $this->expectException(PermissionException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Permission "app.create" not granted (mask: 4, required: 3)
            Permission "user.ban" not granted
            PLAIN
        );
        $roles = new Roles(
            new Role(1, 'user', AppPermission::Create),
            new Role(2, 'admin', AppPermission::Create, UserPermission::Create),
            new Role(4, 'staff', UserPermission::Create),
        );
        $rolesMask = new RolesMask($roles);
        $rolesMask->__invoke(4, AppPermission::Create, UserPermission::Ban);
    }
}
