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

use BackedEnum;
use Chevere\Authorization\Interfaces\PermissionInterface;
use Chevere\Tests\src\EnumIntPermission;
use Chevere\Tests\src\IntPermission;
use Chevere\Tests\src\UserPermission;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function Chevere\Authorization\assertIsPowerOfTwo;
use function Chevere\Authorization\getPermission;

final class FunctionsTest extends TestCase
{
    #[DataProvider('dataProviderPowersOfTwo')]
    public function testAssertIsPowerOfTwo(int $int): void
    {
        $this->expectNotToPerformAssertions();
        assertIsPowerOfTwo($int);
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

    #[DataProvider('dataProviderNotPowerOfTwo')]
    public function testAssertIsPowerOfTwoInvalidArgument(int $int): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            <<<PLAIN
            The number {$int} is not a power of two
            PLAIN
        );
        assertIsPowerOfTwo($int);
    }

    public static function dataProviderNotPowerOfTwo(): array
    {
        return [
            [0],
            [3],
            [5],
            [6],
            [7],
            [9],
            [10],
        ];
    }

    #[DataProvider('dataProviderGetPermission')]
    public function testGetPermission(
        string $expected,
        string|PermissionInterface|BackedEnum $permission
    ): void {
        $this->assertSame($expected, getPermission($permission));
    }

    public static function dataProviderGetPermission(): array
    {
        return [
            ['value', 'value'],
            ['user.create', UserPermission::Create],
            ['1', EnumIntPermission::Create],
            ['4', IntPermission::Create],
        ];
    }
}
