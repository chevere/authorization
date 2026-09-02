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

namespace Chevere\Authorization;

use Chevere\Authorization\Interfaces\PermissionInterface;
use Chevere\Authorization\Interfaces\PermissionsInterface;
use Chevere\Authorization\Interfaces\RoleInterface;
use Chevere\Authorization\Interfaces\RolesInterface;
use Chevere\Caller\Caller;
use Chevere\Caller\Interfaces\CallerInterface;
use InvalidArgumentException;

final class Role implements RoleInterface
{
    private int $mask;

    private PermissionsInterface $permissions;

    private RolesInterface $inherits;

    private PermissionsInterface $grants;

    private CallerInterface $caller;

    /**
     * @param int $bit The bit value for this role. Must be a power of 2.
     * @param string $name The role name (unique).
     * @param PermissionInterface|RoleInterface ...$permit The permissions and roles that this role has.
     */
    final public function __construct(
        private int $bit,
        private string $name,
        PermissionInterface|RoleInterface ...$permit,
    ) {
        if (! ($bit > 0 && ($bit & ($bit - 1)) === 0)) {
            throw new InvalidArgumentException(
                <<<PLAIN
                Argument bit value provided is not a power of two
                PLAIN
            );
        }
        /**
         * @infection-ignore-all
         */
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->caller = Caller::fromArray($trace);
        $this->mask = $this->bit;
        $inherits = [];
        $grants = [];
        foreach ($permit as $item) {
            if ($item instanceof PermissionInterface) {
                $grants[] = $item;

                continue;
            }
            $inherits[] = $item;
        }
        $this->grants = new Permissions(...$grants);
        $this->permissions = $this->grants;
        $this->inherits = new Roles(...$inherits);
        foreach ($this->inherits as $parent) {
            $this->mask += $parent->bit();
            $this->permissions = $this->permissions
                ->withMerge($parent->permissions());
        }
    }

    final public function caller(): CallerInterface
    {
        return $this->caller;
    }

    final public function name(): string
    {
        return $this->name;
    }

    final public function bit(): int
    {
        return $this->bit;
    }

    final public function grants(): PermissionsInterface
    {
        return $this->grants;
    }

    final public function inherits(): RolesInterface
    {
        return $this->inherits;
    }

    final public function mask(): int
    {
        return $this->mask;
    }

    final public function permissions(): PermissionsInterface
    {
        return $this->permissions;
    }
}
