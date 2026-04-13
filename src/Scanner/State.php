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
    protected ?int $activeToken       = null;
    protected int $blockLevel        = 0;
    protected int $extendsMode       = 0;
    protected int $forLevel          = 0;
    protected int $forcedRawState    = 0;
    protected int $ifLevel           = 0;
    protected int $macroLevel        = 0;
    protected ?int $marker            = null;
    protected int $oldIfLevel        = 0;
    protected string $rawBuffer;
    protected string $rawFragment       = '';
    protected int $rawBufferCursor   = 0;
    protected int $startLength;
    protected int $statementPosition = 0;
    protected int $switchLevel       = 0;
    private bool $whitespaceControl = false;
    protected string $activeFile        = 'eval code';
    protected int $activeLine        = 1;
    protected int $cursor            = 0;
    protected ?string $end               = null;
    protected int $mode              = Compiler::PHVOLT_MODE_RAW;
    protected ?string $start             = null;

    public function __construct(string $buffer)
    {
        $this->rawBuffer   = $buffer;
        $this->startLength = mb_strlen($buffer);
        if ($this->startLength > 0) {
            $this->setStart($buffer[0]);
            $this->setEnd($buffer[0]);
        }
    }

    public function appendToRawFragment(string $value): self
    {
        $this->rawFragment .= $value;

        return $this;
    }

    public function decrementBlockLevel(): self
    {
        $this->blockLevel--;

        return $this;
    }

    public function decrementForcedRawState(): self
    {
        $this->forcedRawState--;

        return $this;
    }

    public function decrementForLevel(): self
    {
        $this->forLevel--;

        return $this;
    }

    public function decrementIfLevel(): self
    {
        $this->ifLevel--;

        return $this;
    }

    public function decrementMacroLevel(): self
    {
        $this->macroLevel--;

        return $this;
    }

    public function decrementSwitchLevel(): self
    {
        $this->switchLevel--;

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

    public function getActiveToken(): ?int
    {
        return $this->activeToken;
    }

    public function getBlockLevel(): int
    {
        return $this->blockLevel;
    }

    public function getCursor(): int
    {
        return $this->cursor;
    }

    public function getExtendsMode(): int
    {
        return $this->extendsMode;
    }

    public function getForLevel(): int
    {
        return $this->forLevel;
    }

    public function getForcedRawState(): int
    {
        return $this->forcedRawState;
    }

    public function getIfLevel(): int
    {
        return $this->ifLevel;
    }

    public function getMacroLevel(): int
    {
        return $this->macroLevel;
    }

    public function getMarker(): ?int
    {
        return $this->marker;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getNext(int $increment = 1): ?string
    {
        return $this->rawBuffer[$this->cursor + $increment] ?? null;
    }

    public function getOldIfLevel(): int
    {
        return $this->oldIfLevel;
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

    public function getRawFragment(): string
    {
        return $this->rawFragment;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function getStartLength(): int
    {
        return $this->startLength;
    }

    public function getStatementPosition(): int
    {
        return $this->statementPosition;
    }

    public function getSwitchLevel(): int
    {
        return $this->switchLevel;
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

    public function incrementBlockLevel(): self
    {
        $this->blockLevel++;

        return $this;
    }

    public function incrementForcedRawState(): self
    {
        $this->forcedRawState++;

        return $this;
    }

    public function incrementForLevel(): self
    {
        $this->forLevel++;

        return $this;
    }

    public function incrementIfLevel(): self
    {
        $this->ifLevel++;

        return $this;
    }

    public function incrementMacroLevel(): self
    {
        $this->macroLevel++;

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

    public function incrementStatementPosition(): self
    {
        $this->statementPosition++;

        return $this;
    }

    public function incrementSwitchLevel(): self
    {
        $this->switchLevel++;

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

    public function setActiveToken(?int $activeToken): self
    {
        $this->activeToken = $activeToken;

        return $this;
    }

    public function setBlockLevel(int $blockLevel): self
    {
        $this->blockLevel = $blockLevel;

        return $this;
    }

    public function setCursor(int $cursor): self
    {
        $this->cursor = $cursor;
        $this->setStart($this->rawBuffer[$this->cursor] ?? null);

        return $this;
    }

    public function setEnd(?string $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function setExtendsMode(int $extendsMode): self
    {
        $this->extendsMode = $extendsMode;

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

    public function setMarker(int $marker): self
    {
        $this->marker = $marker;

        return $this;
    }

    public function setMarkerCursor(int $cursor): self
    {
        $this->cursor = $cursor;

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

    public function setRawBufferCursor(int $rawBufferCursor): self
    {
        $this->rawBufferCursor = $rawBufferCursor;

        return $this;
    }

    public function setRawFragment(string $rawFragment): self
    {
        $this->rawFragment = $rawFragment;

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
