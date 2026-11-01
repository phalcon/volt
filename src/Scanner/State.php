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
 * The scanner's position and mode - the scanner half of cphalcon's
 * `phvolt_scanner_state`. The parser-driver half (block levels, extends
 * mode, ...) lives in Phalcon\Volt\Parser\TokenStream.
 *
 * Two values are shared with the driver: the active token (set by the
 * driver after every token; the scanner reads it to tell `a.set` from
 * `{% set %}`) and the statement position (counted by both; the driver uses
 * it to require `{% extends %}` to come first).
 */
final class State
{
    private int $activeLine = 1;

    private ?int $activeToken = null;

    private int $cursor = 0;

    private Mode $mode = Mode::RAW;

    /**
     * Raw text collected since the last fragment was emitted.
     */
    private string $rawFragment = '';

    private int $statementPosition = 0;

    /**
     * Set by `{% verbatim`: the closing `%}` switches to VERBATIM mode.
     */
    private bool $verbatim = false;

    /**
     * Set by `-%}` / `-}}`: left-trim the next raw fragment.
     */
    private bool $whitespaceControl = false;

    public function __construct(private readonly string $code)
    {
    }

    public function appendToRawFragment(string $text): self
    {
        $this->rawFragment .= $text;

        return $this;
    }

    public function getActiveLine(): int
    {
        return $this->activeLine;
    }

    public function getActiveToken(): ?int
    {
        return $this->activeToken;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getCursor(): int
    {
        return $this->cursor;
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function getRawFragment(): string
    {
        return $this->rawFragment;
    }

    public function getStatementPosition(): int
    {
        return $this->statementPosition;
    }

    public function getWhitespaceControl(): bool
    {
        return $this->whitespaceControl;
    }

    public function incrementActiveLine(int $lines = 1): self
    {
        $this->activeLine += $lines;

        return $this;
    }

    public function incrementStatementPosition(): self
    {
        $this->statementPosition++;

        return $this;
    }

    public function isVerbatim(): bool
    {
        return $this->verbatim;
    }

    public function setActiveToken(?int $activeToken): self
    {
        $this->activeToken = $activeToken;

        return $this;
    }

    public function setCursor(int $cursor): self
    {
        $this->cursor = $cursor;

        return $this;
    }

    public function setMode(Mode $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function setRawFragment(string $rawFragment): self
    {
        $this->rawFragment = $rawFragment;

        return $this;
    }

    public function setVerbatim(bool $verbatim): self
    {
        $this->verbatim = $verbatim;

        return $this;
    }

    public function setWhitespaceControl(bool $whitespaceControl): self
    {
        $this->whitespaceControl = $whitespaceControl;

        return $this;
    }
}
