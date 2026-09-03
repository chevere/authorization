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

use Chevere\Authorization\Interfaces\PermissionInterface;
use Chevere\Authorization\Traits\PermissionTrait;

enum PostPermission: string implements PermissionInterface
{
    use PermissionTrait;

    case Create = 'post.create';
    case Delete = 'post.delete';
    case Edit = 'post.edit';
    case View = 'post.view';
}
