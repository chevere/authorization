<?php

/*
 * This file is part of Chevereto Cloud.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevere\Authorization\Interfaces;

interface PermissionInterface
{
    public static function permits(): PermissionsInterface;

    public function value(): string;
}
