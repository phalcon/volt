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

class Token
{
    protected int $opcode = 0;

    protected int $length = 0;

    protected mixed $value = null;

    public function setOpcode(int $opcode): self
    {
        $this->opcode = $opcode;

        return $this;
    }

    public function getOpcode(): int
    {
        return $this->opcode;
    }

    public function setLength(int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
