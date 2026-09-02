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

use Chevere\Action\Action;
use Chevere\Authorization\Interfaces\PermissionInterface;
use Chevere\Authorization\Interfaces\RolesInterface;
use Chevere\DataStructure\Interfaces\StringMappedInterface;
use Chevere\DataStructure\Map;
use Chevere\DataStructure\Traits\MapTrait;
use Chevere\Parameter\Attributes\_int;
use function Chevere\Parameter\Attributes\assertArguments;
use function Chevere\Parameter\int;

/**
 * @implements StringMappedInterface<int>
 */
final class RolesMask extends Action implements StringMappedInterface
{
    /**
     * @template-use MapTrait<int>
     */
    use MapTrait;

    /**
     * @var Map<int> Permission to bit map.
     */
    private Map $map;

    public function __construct(
        private RolesInterface $roles,
    ) {
        $this->map = new Map();
        foreach ($this->roles as $role) {
            foreach ($role->permissions() as $permission) {
                $bit = $role->bit();
                if ($this->map->has($permission->value())) {
                    /** @var int $bit */
                    $bit += $this->map->get($permission->value());
                }
                $this->map = $this->map->withPut($permission->value(), $bit);
            }
        }
    }

    public function roles(): RolesInterface
    {
        return $this->roles;
    }

    /**
     * Asserts that the given mask contains the given permission(s).
     *
     * @throws PermissionException
     */
    public function __invoke(
        #[_int(min: 1)]
        int $bitmask,
        PermissionInterface ...$permission
    ): void {
        assertArguments('bitmask');
        $error = [];
        if (($bitmask & $this->roles->mask()) !== $bitmask) {
            $error[] = sprintf('Bitmask `%d` contains undefined role bits', $bitmask);
        }
        foreach ($permission as $item) {
            if (! $this->map->has($item->value())) {
                $error[] = sprintf('Permission `%s` not granted', $item->value());

                continue;
            }
            $allowMask = $this->map->get($item->value());
            if (($allowMask & $bitmask) === 0) {
                $error[] = sprintf(
                    'Permission `%s` not granted (mask: `%d`, required: `%d`)',
                    $item->value(),
                    $bitmask,
                    $allowMask
                );
            }
        }

        if ($error !== []) {
            throw new PermissionException(
                implode(PHP_EOL, $error)
            );
        }
    }

    /**
     * Returns true if the given mask contains the given permission(s).
     */
    public function contains(
        int $bitmask,
        PermissionInterface ...$permission
    ): bool {
        foreach ($permission as $item) {
            if (! $this->map->has($item->value())) {
                return false;
            }
            $permMask = $this->map->get($item->value());
            if (($permMask & $bitmask) === 0) {
                return false;
            }
        }

        return true;
    }
}
