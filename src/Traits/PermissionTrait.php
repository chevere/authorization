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

namespace Chevere\Authorization\Traits;

use Chevere\Authorization\Interfaces\PermissionsInterface;
use Chevere\Authorization\Permissions;

trait PermissionTrait
{
    abstract public static function cases(): array;

    public static function permits(): PermissionsInterface
    {
        return new Permissions(...static::cases());
    }

    public function value(): string
    {
        return $this->value;
    }
}
