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

namespace Chevere\Authorization\Interfaces;

use Chevere\Caller\Interfaces\CallerInterface;

interface RoleInterface
{
    /**
     * The role name (unique).
     */
    public function name(): string;

    /**
     * The bit value for this role.
     * Must be a power of 2.
     */
    public function bit(): int;

    /**
     * The roles that this role inherits.
     */
    public function inherits(): RolesInterface;

    /**
     * The mask value for this role.
     * Must be the role bit + the sum of all the bits from inherited roles.
     */
    public function mask(): int;

    /**
     * The permissions exclusively granted by this role.
     * This doesn't include the permissions from the inherited roles.
     */
    public function grants(): PermissionsInterface;

    /**
     * The permissions that this role has.
     * This includes the permissions from the inherited roles.
     */
    public function permissions(): PermissionsInterface;

    /**
     * The caller for this role.
     */
    public function caller(): CallerInterface;
}
