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

namespace Chevere\Tests\src;

enum EnumPermission: string
{
    case Create = 'enum.create';
    case Ban = 'enum.ban';
    case Delete = 'enum.delete';
}
