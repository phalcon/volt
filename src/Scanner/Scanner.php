<?php

declare(strict_types=1);

namespace Phalcon\Volt\Scanner;

use Phalcon\Volt\Compiler;

class Scanner
{
    public const PHVOLT_SCANNER_RETCODE_EOF = -1;
    public const PHVOLT_SCANNER_RETCODE_ERR = -2;
    public const PHVOLT_SCANNER_RETCODE_IMPOSSIBLE = -3;

    private Token $token;

    public function __construct(private State $state)
    {
        $this->token = new Token();
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function scanForToken()
    {
        $status = self::PHVOLT_SCANNER_RETCODE_IMPOSSIBLE;

        while (self::PHVOLT_SCANNER_RETCODE_IMPOSSIBLE === $status) {
            $cursor = $this->state->getStart();
            $mode = $this->state->getMode();
            if ($mode === Compiler::PHVOLT_MODE_RAW || $mode === Compiler::PHVOLT_MODE_COMMENT) {
                $next = "\0";
                $doubleNext = "\0";

                if ($cursor === "\n") {
                    $this->state->incrementActiveLine();
                }

                if ($cursor !== "\0") {
                    $next = $this->state->incrementStart()->getStart();
                    if ($next !== "\0") {
                        $doubleNext = $this->state->incrementStart(2)->getStart();
                    }
                }

                if ($cursor === "\0" || ($cursor === '{' && ($next === '%' || $next === '{' || $next === '#'))) {
                    if ($next !== '#') {
                        $this->state->setMode(Compiler::PHVOLT_MODE_CODE);

                        if ($this->state->getRawBufferCursor() > 0) {
                            $this
                                ->token
                                ->setOpcode(Compiler::PHVOLT_T_RAW_FRAGMENT)
                                ->setValue(0);

                            if ($this->state->getWhitespaceControl()) {
                                //ltrim(); // TODO
                                $this->state->setWhitespaceControl(false);
                            }

                            if ($doubleNext === '-') {
                                // rtrim($token); // TODO
                            }

                            $this->state->setRawBufferCursor(0);
                        } else {
                            $this->token->setOpcode(Compiler::PHVOLT_T_IGNORE);
                        }
                    } else {
                        while ($next = $this->state->incrementStart()->getStart()) {
                            if ($next === "\n") {
                                $this->state->incrementActiveLine();
                            }
                        }
                    }
                }
            }
        }
    }
}
