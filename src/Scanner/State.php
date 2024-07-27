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

use Phalcon\Volt\Compiler;

class State
{
    public int $activeToken = 0;

    protected int $mode = Compiler::PHVOLT_MODE_RAW;

    protected ?string $start = null;

    protected int $cursor = 0;

    protected ?string $end = null;

    public string $marker;

    public int $startLength;

    protected int $activeLine = 1;

    public string $activeFile;

    public int $statementPosition = 0;

    public int $extendsMode = 0;

    public int $blockLevel = 0;

    public int $macroLevel = 0;

    public string $rawBuffer;

    public int $rawBufferCursor = 0;

    public int $rawBufferSize = Compiler::PHVOLT_RAW_BUFFER_SIZE;

    public int $oldIfLevel = 0;

    public int $ifLevel = 0;

    public int $forLevel = 0;

    public int $switchLevel = 0;

    public bool $whitespaceControl = false;

    public int $forcedRawState = 0;

    public function __construct(string $buffer)
    {
        $this->rawBuffer = $buffer;
        $this->startLength = mb_strlen($buffer);
        if ($this->startLength > 0) {
            $this->setStart($buffer[0]);
            $this->setEnd($buffer[0]);
        }
    }

    public function setActiveToken(int $activeToken): self
    {
        $this->activeToken = $activeToken;

        return $this;
    }

    public function setMode(int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function setStart(?string $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getStart(): string
    {
        return $this->start;
    }

    public function getNext(int $increment = 1): ?string
    {
        return $this->rawBuffer[$this->cursor + $increment] ?? null;
    }

    public function incrementStart(int $value = 1): self
    {
        $this->cursor += $value;
        $this->setStart($this->rawBuffer[$this->cursor] ?? null);

        return $this;
    }

    public function setEnd(string $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function setMarker(string $marker): self
    {
        $this->marker = $marker;

        return $this;
    }

    public function setStartLength(int $startLength): self
    {
        $this->startLength = $startLength;

        return $this;
    }

    public function setActiveLine(int $activeLine): self
    {
        $this->activeLine = $activeLine;

        return $this;
    }

    public function incrementActiveLine(): self
    {
        $this->activeLine++;

        return $this;
    }

    public function setActiveFile(string $activeFile): self
    {
        $this->activeFile = $activeFile;

        return $this;
    }

    public function setStatementPosition(int $statementPosition): self
    {
        $this->statementPosition = $statementPosition;

        return $this;
    }

    public function extendsMode(int $extendsMode): self
    {
        $this->extendsMode = $extendsMode;

        return $this;
    }

    public function setBlockLevel(int $blockLevel): self
    {
        $this->blockLevel = $blockLevel;

        return $this;
    }

    public function setMacroLevel(int $macroLevel): self
    {
        $this->macroLevel = $macroLevel;

        return $this;
    }

    public function setRawBuffer(string $rawBuffer): self
    {
        $this->rawBuffer = $rawBuffer;

        return $this;
    }

    public function setRawBufferCursor(int $rawBufferCursor): self
    {
        $this->rawBufferCursor = $rawBufferCursor;

        return $this;
    }

    public function getRawBufferCursor(): int
    {
        return $this->rawBufferCursor;
    }

    public function setRawBufferSize(int $rawBufferSize): self
    {
        $this->rawBufferSize = $rawBufferSize;

        return $this;
    }

    public function setOldIfLevel(int $oldIfLevel): self
    {
        $this->oldIfLevel = $oldIfLevel;

        return $this;
    }

    public function setIfLevel(int $ifLevel): self
    {
        $this->ifLevel = $ifLevel;

        return $this;
    }

    public function setForLevel(int $forLevel): self
    {
        $this->forLevel = $forLevel;

        return $this;
    }

    public function setSwitchLevel(int $switchLevel): self
    {
        $this->switchLevel = $switchLevel;

        return $this;
    }

    public function setWhitespaceControl(bool $whitespaceControl): self
    {
        $this->whitespaceControl = $whitespaceControl;

        return $this;
    }

    public function getWhitespaceControl(): bool
    {
        return $this->whitespaceControl;
    }

    public function setForcedRawState(int $forcedRawState): self
    {
        $this->forcedRawState = $forcedRawState;

        return $this;
    }
}
