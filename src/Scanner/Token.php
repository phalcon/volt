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

final class Token
{
    public readonly int $length;

    public function __construct(
        public readonly int $opcode = 0,
        public readonly ?string $value = null,
        int $length = 0,
    ) {
        $this->length = ($length === 0 && $value !== null) ? mb_strlen($value) : $length;
    }
}
