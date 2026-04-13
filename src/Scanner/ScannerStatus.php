<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Volt\Scanner;

enum ScannerStatus: int
{
    case EOF = -1;
    case ERR = -2;
    case IMPOSSIBLE = -3;
    case OK = 0;
}
