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

use Chevere\Tests\src\UserPermission;
use PHPUnit\Framework\TestCase;

final class PermissionTest extends TestCase
{
    public function testUserPermission(): void
    {
        $this->assertSame(
            UserPermission::cases(),
            iterator_to_array(UserPermission::permits())
        );
    }

    public function testValue(): void
    {
        $this->assertSame(
            UserPermission::Create->value,
            UserPermission::Create->value()
        );
    }
}
