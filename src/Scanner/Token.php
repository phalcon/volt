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

/**
 * One scanned token: its opcode (a Phalcon\Volt\Compiler\Opcode value, or 0
 * for end of input) and, for identifiers, literals, raw text and the
 * `default` keyword, its source text.
 */
final class Token
{
    public function __construct(
        public readonly int $opcode = 0,
        public readonly ?string $value = null,
    ) {
    }
}
