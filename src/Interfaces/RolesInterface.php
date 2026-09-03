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

use Chevere\DataStructure\Interfaces\StringMappedInterface;
use Iterator;

/**
 * Describes the component in charge of collecting objects of RoleInterface.
 *
 * @extends StringMappedInterface<RoleInterface>
 */
interface RolesInterface extends StringMappedInterface
{
    public function __invoke(PermissionInterface ...$permission): bool;

    public function mask(): int;

    /**
     * @return Iterator<string, RoleInterface>
     */
    public function getIterator(): Iterator;

    public function find(string $name): RoleInterface;

    public function has(int ...$bit): bool;

    public function get(int $bit): RoleInterface;

    public function forMask(int $mask): self;

    public function permissions(): PermissionsInterface;
}
