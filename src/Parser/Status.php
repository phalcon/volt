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

    /**
     * @var string|null
     */
    protected ?string $syntaxError = null;
    /**
     * @var Token|null
     */
    protected ?Token $token = null;

    public function __construct(
        protected State $scannerState,
        protected int $status = self::PHVOLT_PARSING_OK,
    ) {
    }

    /**
     * @return State
     */
    public function getState(): State
    {
        return $this->scannerState;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getSyntaxError(): ?string
    {
        return $this->syntaxError;
    }

    /**
     * @return Token|null
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param string $syntaxError
     *
     * @return $this
     */
    public function setSyntaxError(string $syntaxError): self
    {
        $this->syntaxError = $syntaxError;

        return $this;
    }

    /**
     * @param Token $token
     *
     * @return $this
     */
    public function setToken(Token $token): self
    {
        $this->token = $token;

        return $this;
    }
}
