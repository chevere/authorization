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

use Countable;
use Iterator;
use IteratorAggregate;

/**
 * Describes the component in charge of collecting PermissionInterface objects.
 *
 * @extends IteratorAggregate<PermissionInterface>
 */
interface PermissionsInterface extends IteratorAggregate, Countable
{
    public function contains(PermissionInterface ...$permission): bool;

    public function assert(PermissionInterface ...$permission): void;

    /**
     * Return an instance with the specified $permissions merged.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the specified $permissions merged.
     */
    public function withMerge(self $permissions): self;

    /**
     * @return Iterator<PermissionInterface>
     */
    public function getIterator(): Iterator;
}
