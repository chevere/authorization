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
     * @return string The role name.
     */
    public function name(): string;

    /**
     * The bit value for this role.
     *
     * @return int Must be a power of 2.
     */
    public function bit(): int;

    /**
     * @return RolesInterface The roles that this role inherits.
     */
    public function inherits(): RolesInterface;

    /**
     * Must be the role bit + the sum of all the bits from inherited roles.
     *
     * @return int The mask value for this role.
     */
    public function mask(): int;

    /**
     * This doesn't include the permissions from the inherited roles.
     *
     * @return PermissionsInterface The permissions exclusively granted by this role.
     */
    public function grants(): PermissionsInterface;

    /**
     * This includes the permissions from the inherited roles.
     *
     * @return PermissionsInterface The permissions that this role has.
     */
    public function permissions(): PermissionsInterface;

    /**
     * @return CallerInterface The caller for this role.
     */
    public function caller(): CallerInterface;
}
