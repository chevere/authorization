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

use Chevere\Authorization\Interfaces\RoleInterface;
use Chevere\Authorization\Role;
use Chevere\Caller\Caller;
use Chevere\Tests\src\UserPermission;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Chevere\Standard\getBits;

final class RoleTest extends TestCase
{
    #[DataProvider('dataProviderPowersOfTwo')]
    public function testPowersOfTwo(int $power): void
    {
        $role = new Role($power, 'valid');
        $this->assertSame($power, $role->bit());
    }

    public static function dataProviderPowersOfTwo(): array
    {
        $powers = [];
        $maxValue = strval(PHP_INT_MAX);
        for ($i = 0; $i <= 63; $i++) {
            $value = bcpow('2', (string) $i);
            if (bccomp($value, $maxValue) <= 0) {
                $powers[] = [intval($value)];
            } else {
                break;
            }
        }

        return $powers;
    }

    #[DataProvider('dataProviderNoPowerOfTwo')]
    public function testNoPowerOfTwo(int $int): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            Argument bit value provided is not a power of two
            PLAIN
        );
        new Role($int, 'invalid');
    }

    public static function dataProviderNoPowerOfTwo(): array
    {
        return [
            [0],
            [3],
            [5],
            [6],
            [7],
            [9],
            [10],
            [12],
            [14],
            [15],
        ];
    }

    #[DataProvider('dataProviderRole')]
    public function testRole(
        int $bit,
        int $mask,
        int $line,
        RoleInterface $role
    ): void {
        $this->assertEquals(
            new Caller(__FILE__, $line),
            $role->caller()
        );
        $this->assertSame($bit, $role->bit());
        $this->assertSame($mask, $role->mask());
        $bits = getBits($mask);
        $inherits = iterator_to_array($role->inherits());
        array_unshift($inherits, $role);
        $result = [];
        foreach ($inherits as $parent) {
            $result[] = $parent->bit();
        }
        asort($result);
        $result = array_values($result);
        $this->assertSame($result, $bits);
    }

    public function testInheritsMaskAndPermissions(): void
    {
        $user = new Role(1, 'user', UserPermission::Create);
        $staff = new Role(4, 'staff', UserPermission::Ban);
        $admin = new Role(2, 'admin', $user, $staff);
        $this->assertSame(1 + 2 + 4, $admin->mask());
        $this->assertTrue($admin->permissions()->contains(UserPermission::Create));
        $this->assertTrue($admin->permissions()->contains(UserPermission::Ban));
    }

    public static function dataProviderRole(): array
    {
        return [
            [
                1,
                1,
                __LINE__ + 1,
                new Role(1, 'user'),
            ],
            [
                2,
                7,
                __LINE__ + 1,
                new Role(2, 'admin', new Role(1, 'user'), new Role(4, 'staff')),
            ],
        ];
    }
}
