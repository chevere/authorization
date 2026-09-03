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
use Chevere\DataStructure\Map;
use Chevere\DataStructure\Traits\MapTrait;
use OutOfBoundsException;
use OverflowException;
use function Chevere\Message\message;

final class Roles implements RolesInterface
{
    /**
     * @template-use MapTrait<RoleInterface>
     */
    use MapTrait;

    /**
     * Computed permissions from roles.
     */
    private PermissionsInterface $permissions;

    /**
     * @var Map<int>
     */
    private Map $names;

    private int $mask;

    public function __construct(RoleInterface ...$role)
    {
        $this->map = new Map();
        $this->names = new Map();
        $this->permissions = new Permissions();
        $this->mask = 0;
        foreach ($role as $item) {
            $this->put($item);
        }
    }

    public function __invoke(PermissionInterface ...$permission): bool
    {
        return $this->permissions->contains(...$permission);
    }

    public function mask(): int
    {
        return $this->mask;
    }

    public function find(string $name): RoleInterface
    {
        if (! $this->names->has($name)) {
            throw new OutOfBoundsException(
                sprintf('Role with name "%s" does not exist.', $name)
            );
        }

        return $this->map->get($this->names->get($name));
    }

    public function has(int ...$bit): bool
    {
        foreach ($bit as $value) {
            if ($value <= 0 || ($this->mask & $value) !== $value) {
                return false;
            }
        }

        return true;
    }

    public function get(int $bit): RoleInterface
    {
        // @var RoleInterface
        return $this->map->get($bit);
    }

    public function forMask(int $mask): RolesInterface
    {
        $roles = [];
        foreach ($this as $role) {
            if ($role->bit() & $mask) {
                $roles[] = $role;
            }
        }

        return new self(...$roles);
    }

    public function permissions(): PermissionsInterface
    {
        return $this->permissions;
    }

    private function put(RoleInterface $role): void
    {
        if ($this->names->has($role->name())) {
            $conflict = $this->map->get(
                $this->names->get($role->name())
            );

            throw new OverflowException(
                (string) message(
                    'Role **%name%** has been previously taken by %caller%',
                    name: $role->name(),
                    caller: $conflict->caller()
                        ->__toString(),
                )
            );
        }

        try {
            /** @var RoleInterface $conflict */
            $conflict = $this->map->get($role->bit());

            throw new OverflowException(
                (string) message(
                    'Bit **%bit%** has been previously reserved by role `%role%` in %caller%',
                    bit: strval($role->bit()),
                    role: $conflict->name(),
                    caller: $conflict->caller()
                        ->__toString(),
                )
            );
        } catch (OutOfBoundsException) {
        }
        $this->map = $this->map->withPut($role->bit(), $role);
        $this->mask += $role->bit();
        $this->names = $this->names->withPut($role->name(), $role->bit());
        $this->permissions = $this->permissions
            ->withMerge($role->permissions());
    }
}
