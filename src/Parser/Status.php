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

namespace Phalcon\Volt\Parser;

use Phalcon\Volt\Scanner\State;
use Phalcon\Volt\Scanner\Token;

class Status
{
    public const PHVOLT_PARSING_OK = 1;
    public const PHVOLT_PARSING_FAILED = 0;

    protected $ret = null;

    protected State $scannerState;

    protected ?Token $token = null;

    protected int $status;

    protected ?string $syntaxError = null;

    public function __construct(
        State $scannerState,
        int   $status = self::PHVOLT_PARSING_OK,
    ) {
        $this->scannerState = $scannerState;
        $this->status = $status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getState(): State
    {
        return $this->scannerState;
    }

    public function setToken(Token $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setSyntaxError(string $syntaxError): self
    {
        $this->syntaxError = $syntaxError;

        return $this;
    }

    public function getSyntaxError(): ?string
    {
        return $this->syntaxError;
    }
}
