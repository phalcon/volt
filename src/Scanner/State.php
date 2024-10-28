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
    public mixed $activeToken = null;
    public int $blockLevel = 0;
    public int $extendsMode = 0;
    public int $forLevel = 0;
    public int $forcedRawState = 0;
    public int $ifLevel = 0;
    public int $macroLevel = 0;
    public int $marker = 0;
    public int $oldIfLevel = 0;
    public string $rawBuffer;
    public string $rawFragment = '';
    public int $rawBufferCursor = 0;
    public int $startLength;
    public int $statementPosition = 0;
    public int $switchLevel = 0;
    private bool $whitespaceControl = false;
    protected string $activeFile = 'eval code';
    protected int $activeLine = 1;
    protected int $cursor = 0;
    protected ?string $end = null;
    protected int $mode = Compiler::PHVOLT_MODE_RAW;
    protected ?string $start = null;

    public function __construct(string $buffer)
    {
        $this->rawBuffer   = $buffer;
        $this->startLength = mb_strlen($buffer);
        if ($this->startLength > 0) {
            $this->setStart($buffer[0]);
            $this->setEnd($buffer[0]);
        }
    }

    public function extendsMode(int $extendsMode): self
    {
        $this->extendsMode = $extendsMode;

        return $this;
    }

    public function getActiveFile(): string
    {
        return $this->activeFile;
    }

    public function getActiveLine(): int
    {
        return $this->activeLine;
    }

    public function getActiveToken(): mixed
    {
        return $this->activeToken;
    }

    public function getCursor(): int
    {
        return $this->cursor;
    }

    public function getIfLevel(): int
    {
        return $this->ifLevel;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getNext(int $increment = 1): ?string
    {
        return $this->rawBuffer[$this->cursor + $increment] ?? null;
    }

    public function getPrevious(int $decrement = 1): ?string
    {
        return $this->rawBuffer[$this->cursor - $decrement] ?? null;
    }

    public function getRawBuffer(): string
    {
        return $this->rawBuffer;
    }

    public function getRawBufferCursor(): int
    {
        return $this->rawBufferCursor;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function getStartLength(): int
    {
        return $this->startLength;
    }

    public function getWhitespaceControl(): bool
    {
        return $this->whitespaceControl;
    }

    public function incrementActiveLine(): self
    {
        $this->activeLine++;

        return $this;
    }

    public function incrementRawBufferCursor(): self
    {
        $this->rawBufferCursor++;

        return $this;
    }

    public function incrementStart(int $value = 1): self
    {
        $this->cursor += $value;
        $this->setStart($this->rawBuffer[$this->cursor] ?? null);

        return $this;
    }

    public function setActiveFile(string $activeFile): self
    {
        $this->activeFile = $activeFile;

        return $this;
    }

    public function setActiveLine(int $activeLine): self
    {
        $this->activeLine = $activeLine;

        return $this;
    }

    public function setActiveToken(mixed $activeToken): self
    {
        $this->activeToken = $activeToken;

        return $this;
    }

    public function setBlockLevel(int $blockLevel): self
    {
        $this->blockLevel = $blockLevel;

        return $this;
    }

    public function setEnd(?string $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function setForLevel(int $forLevel): self
    {
        $this->forLevel = $forLevel;

        return $this;
    }

    public function setForcedRawState(int $forcedRawState): self
    {
        $this->forcedRawState = $forcedRawState;

        return $this;
    }

    public function setIfLevel(int $ifLevel): self
    {
        $this->ifLevel = $ifLevel;

        return $this;
    }

    public function setMacroLevel(int $macroLevel): self
    {
        $this->macroLevel = $macroLevel;

        return $this;
    }

    public function setMode(int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function setOldIfLevel(int $oldIfLevel): self
    {
        $this->oldIfLevel = $oldIfLevel;

        return $this;
    }

    public function setRawBuffer(string $rawBuffer): self
    {
        $this->rawBuffer = $rawBuffer;

        return $this;
    }

    public function setStart(?string $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function setStartLength(int $startLength): self
    {
        $this->startLength = $startLength;

        return $this;
    }

    public function setStatementPosition(int $statementPosition): self
    {
        $this->statementPosition = $statementPosition;

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
}
