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

use Chevere\Action\Interfaces\ActionInterface;
use Chevere\Authorization\PermissionException;
use Chevere\DataStructure\Interfaces\StringMappedInterface;

/**
 * Describes the component in charge of asserting role masks against permissions.
 *
 * @extends StringMappedInterface<int>
 */
interface RolesMaskInterface extends ActionInterface, StringMappedInterface
{
    /**
     * Provides access to the Roles instance.
     */
    public function roles(): RolesInterface;

    /**
     * Asserts that the given `$bitmask` contains the given `...$permission`.
     *
     * @throws PermissionException
     */
    public function __invoke(
        int $bitmask,
        PermissionInterface ...$permission
    ): void;

    /**
     * @return bool True if the given `$bitmask` contains the given `...$permission`.
     */
    public function contains(
        int $bitmask,
        PermissionInterface ...$permission
    ): bool;
}
