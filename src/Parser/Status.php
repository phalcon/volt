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
    public const PHVOLT_PARSING_FAILED = 0;
    public const PHVOLT_PARSING_OK     = 1;

    private ?string $lastTokenValue = null;
    private ?string $syntaxError    = null;
    private ?Token $token           = null;

    public function __construct(
        private State $scannerState,
        private int $status = self::PHVOLT_PARSING_OK,
    ) {
    }

    public function getState(): State
    {
        return $this->scannerState;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getSyntaxError(): ?string
    {
        return $this->syntaxError;
    }

    public function getLastTokenValue(): ?string
    {
        return $this->lastTokenValue;
    }

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setSyntaxError(string $syntaxError): static
    {
        $this->syntaxError = $syntaxError;

        return $this;
    }

    public function setToken(Token $token): static
    {
        $this->token = $token;
        if ($token->value !== null) {
            $this->lastTokenValue = $token->value;
        }

        return $this;
    }
}
