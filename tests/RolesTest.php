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
        $userRole = new Role(1, 'user');
        $role = new Role($bit, $role);
        $this->expectException(OverflowException::class);
        $this->expectExceptionMessage($error);
        new Roles($userRole, $role);
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
        $userRole = new Role(1, 'user');
        $adminRole = new Role(2, 'admin');
        $roles = new Roles($userRole, $adminRole);
        $this->assertCount(2, $roles);
        $this->assertSame(
            [1, 2],
            $roles->keys()
        );
        $this->assertSame($userRole, $roles->get(1));
        $this->assertSame($adminRole, $roles->get(2));
        $this->assertTrue(
            $roles->permissions()
                ->contains(
                    ...$userRole->grants(),
                    ...$adminRole->grants()
                )
        );
        $this->assertSame(3, $roles->mask());
        $this->assertTrue($roles->has(3, 2, 1));
        $this->assertFalse($roles->has(3, 2, 1, 0));
    }

    public function testPermissionsContains(): void
    {
        $userRole = new Role(1, 'user', 'post.draft');
        $adminRole = new Role(2, 'admin', 'post.publish');
        $roles = new Roles($userRole, $adminRole);
        $this->assertTrue(
            $roles->permissions()
                ->contains('post.draft', 'post.publish')
        );
        $this->assertFalse(
            $roles->permissions()
                ->contains('post.draft', 'post.publish', 'post.delete')
        );
    }

    public function testHasCompositeBitRequiresAllBits(): void
    {
        $roles = new Roles(new Role(1, 'user'));

        $this->assertFalse($roles->has(1 | 2));
    }

    public function testFind(): void
    {
        $userRole = new Role(1, 'user');
        $adminRole = new Role(2, 'admin');
        $roles = new Roles($userRole, $adminRole);
        $this->assertSame($userRole, $roles->find('user'));
        $this->assertSame($adminRole, $roles->find('admin'));
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
        $userRole = new Role(1, 'user');
        $adminRole = new Role(2, 'admin');
        $staffRole = new Role(4, 'staff');
        $roles = new Roles($adminRole, $staffRole);
        $mask = $adminRole->bit() + $staffRole->bit();
        $this->assertSame(
            [2, 4],
            $roles->forMask($mask)
                ->keys()
        );
        $this->assertTrue(
            $roles->permissions()
                ->contains(
                    ...$userRole->grants(),
                    ...$adminRole->grants(),
                    ...$staffRole->grants()
                )
        );
    }
}
