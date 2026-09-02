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

namespace Chevere\Authorization;

use Exception;

/**
 * Exception thrown when a user does not have the required permissions.
 */
final class PermissionException extends Exception
{
}
