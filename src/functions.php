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

use InvalidArgumentException;

function assertIsPowerOfTwo(int $number): void
{
    if ($number <= 0 || ($number & ($number - 1)) !== 0) {
        throw new InvalidArgumentException("The number {$number} is not a power of two.");
    }
}
