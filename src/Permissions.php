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
use Chevere\DataStructure\Interfaces\VectorInterface;
use Chevere\DataStructure\Vector;
use Iterator;
use LogicException;
use function Chevere\Message\message;

final class Permissions implements PermissionsInterface
{
    private VectorInterface $names;

    private VectorInterface $items;

    public function __construct(
        PermissionInterface ...$permission
    ) {
        $this->names = new Vector();
        $this->items = new Vector();
        $this->pushPermission(...$permission);
    }

    public function count(): int
    {
        return $this->names->count();
    }

    public function assert(PermissionInterface ...$permission): void
    {
        $missing = [];
        foreach ($permission as $item) {
            $value = $item->value();
            if (! $this->names->contains($value)) {
                $missing[] = $value;
            }
        }
        if ($missing === []) {
            return;
        }

        throw new LogicException(
            (string) message(
                <<<PLAIN
                Missing permission(s): %s%
                PLAIN
                ,
                s: implode(', ', $missing)
            )
        );
    }

    public function contains(PermissionInterface ...$permission): bool
    {
        foreach ($permission as $item) {
            $value = $item->value();
            if (! $this->names->contains($value)) {
                return false;
            }
        }

        return true;
    }

    public function getIterator(): Iterator
    {
        return $this->items->getIterator();
    }

    public function withMerge(PermissionsInterface $permissions): self
    {
        $new = clone $this;
        $new->pushPermission(...$permissions);

        return $new;
    }

    private function pushPermission(PermissionInterface ...$permission): void
    {
        foreach ($permission as $item) {
            $value = $item->value();
            if ($this->names->contains($value)) {
                continue;
            }
            $this->names = $this->names->withPush($value);
            $this->items = $this->items->withPush($item);
        }
    }
}
