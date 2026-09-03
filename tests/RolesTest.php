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

use Chevere\Authorization\Role;
use Chevere\Authorization\Roles;
use OutOfBoundsException;
use OverflowException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RolesTest extends TestCase
{
    public function testEmpty(): void
    {
        $roles = new Roles();
        $this->assertCount(0, $roles);
        $this->assertSame(0, $roles->mask());
    }

    #[DataProvider('dataProviderOverflow')]
    public function testOverflow(int $bit, string $role, string $error): void
    {
        $user = new Role(1, 'user');
        $role = new Role($bit, $role);
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage($error);
        new Roles($user, $role);
    }

    public static function dataProviderOverflow(): array
    {
        return [
            [1, 'role', 'Bit **1** has been previously reserved by role `user` in'],
            [2, 'user', 'Role **user** has been previously taken by'],
        ];
    }

    public function testConstruct(): void
    {
        $user = new Role(1, 'user');
        $admin = new Role(2, 'admin');
        $roles = new Roles($user, $admin);
        $this->assertCount(2, $roles);
        $this->assertSame(
            [1, 2],
            $roles->keys()
        );
        $this->assertSame($user, $roles->get(1));
        $this->assertSame($admin, $roles->get(2));
        $this->assertTrue(
            $roles(
                ...$user->grants(),
                ...$admin->grants()
            )
        );
        $this->assertSame(3, $roles->mask());
        $this->assertTrue($roles->has(3, 2, 1));
        $this->assertFalse($roles->has(3, 2, 1, 0));
    }

    public function testHasCompositeBitRequiresAllBits(): void
    {
        $roles = new Roles(new Role(1, 'user'));

        $this->assertFalse($roles->has(1 | 2));
    }

    public function testFind(): void
    {
        $user = new Role(1, 'user');
        $admin = new Role(2, 'admin');
        $roles = new Roles($user, $admin);
        $this->assertSame($user, $roles->find('user'));
        $this->assertSame($admin, $roles->find('admin'));
    }

    public function testFindNotFound(): void
    {
        $roles = new Roles(new Role(1, 'user'));
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Role with name "unknown" does not exist.');
        $roles->find('unknown');
    }

    public function testForMask(): void
    {
        $user = new Role(1, 'user');
        $admin = new Role(2, 'admin');
        $staff = new Role(4, 'staff');
        $roles = new Roles($admin, $staff);
        $mask = $admin->bit() + $staff->bit();
        $this->assertSame(
            [2, 4],
            $roles->forMask($mask)
                ->keys()
        );
        $this->assertTrue(
            $roles(
                ...$user->grants(),
                ...$admin->grants(),
                ...$staff->grants()
            )
        );
    }
}
