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
    protected mixed $opcode = null;

    protected int $length = 0;

    protected mixed $value = null;

    protected bool $freeFlag = false;

    public function setOpcode(mixed $opcode): self
    {
        $this->opcode = $opcode;

        return $this;
    }

    public function getOpcode(): mixed
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
        if (!empty($value)) {
            $this->setLength(mb_strlen($value));
        }

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setFreeFlag(bool $free): self
    {
        $this->freeFlag = $free;

        return $this;
    }

    public function getFreeFlag(): bool
    {
        return $this->freeFlag;
    }
}
