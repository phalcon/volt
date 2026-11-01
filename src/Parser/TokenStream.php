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

use Phalcon\Volt\Compiler\Opcode;
use Phalcon\Volt\Exception;
use Phalcon\Volt\Scanner\Scanner;
use Phalcon\Volt\Scanner\ScannerStatus;
use Phalcon\Volt\Scanner\State;
use Phalcon\Volt\Scanner\Token;
use Phalcon\Volt\Tokens;

use function sprintf;
use function strlen;
use function strpos;
use function strspn;
use function substr;

/**
 * Port of the token loop in cphalcon's `phvolt_internal_parse_view()`
 * (ext/phalcon/mvc/view/engine/volt/base.c lines 300-800): it pulls tokens
 * from the scanner ON DEMAND and, for each one, does what base.c does before
 * handing it to the parser:
 *
 *  - records the active token and how much input is left after it (both are
 *    read back by the syntax-error message);
 *  - runs the template checks (child templates may only contain blocks,
 *    nested switch / block / macro, extends must come first, ...) - these
 *    throw before the token reaches the parser;
 *  - remaps tokens: ELSE inside a `for` (and no `if`) becomes ELSEFOR, and
 *    DEFAULT is the switch clause only right after `{%` inside a switch;
 *  - drops what the parser never sees: whitespace, empty raw fragments and
 *    blank raw fragments at the top level of a child template.
 *
 * Scanning on demand rather than up front matters: a check on a later token
 * must not pre-empt a syntax error that cphalcon reports on an earlier one.
 *
 * The level counters are C `unsigned int`s and wrap the same way.
 */
final class TokenStream
{
    /**
     * Blank characters for raw fragments (phvolt_is_blank_string).
     */
    private const BLANK = " \t\n\r\v";

    private const UINT_MASK = 0xFFFFFFFF;

    private int $blockLevel = 0;

    private readonly string $code;

    private bool $extendsMode = false;

    private int $forLevel = 0;

    private int $ifLevel = 0;

    private int $macroLevel = 0;

    private int $oldIfLevel = 0;

    /**
     * Opcode of the last scanned token that was not whitespace.
     */
    private int $previous = 0;

    private readonly Scanner $scanner;

    /**
     * base.c `state->start_length`: bytes left after the last scanned token.
     */
    private int $startLength = 0;

    private readonly State $state;

    private int $switchLevel = 0;

    /**
     * The scanner token's value as cphalcon's syntax-error message sees it:
     * set when a value token is scanned, cleared once the parser has
     * consumed it (phvolt_parse_with_token).
     *
     * In three places base.c frees the value without clearing the pointer,
     * and a later syntax error prints the dangling value with its old length:
     *  - a raw fragment trimmed to nothing by whitespace control has length
     *    0, so C deterministically prints "()" - reproduced here;
     *  - a blank raw fragment dropped from a child template, and the
     *    `{% default %}` keyword, have a non-zero length, so C prints freed
     *    heap memory - undefined behaviour that this port does not imitate
     *    (it prints no value).
     */
    private ?string $value = null;

    public function __construct(string $code, private readonly string $file)
    {
        $this->code    = $code;
        $this->state   = new State($code);
        $this->scanner = new Scanner($this->state);
    }

    /**
     * The parser has consumed $token (cphalcon clears the token value after a
     * value-carrying token has been fed).
     */
    public function consumed(Token $token): void
    {
        if ($token->value !== null) {
            $this->value = null;
        }
    }

    public function file(): string
    {
        return $this->file;
    }

    /**
     * The scanner's current line - the line Lemon stamps on a node reduced now.
     */
    public function line(): int
    {
        return $this->state->getActiveLine();
    }

    /**
     * The next token for the parser, or an EOF token (opcode 0).
     *
     * @throws Exception on a scanning error or a failed template check
     */
    public function next(): Token
    {
        while (true) {
            $status = $this->scanner->scanForToken();

            if ($status === ScannerStatus::EOF) {
                return new Token(0);
            }

            if ($status !== ScannerStatus::OK) {
                throw new Exception($this->scanningErrorMessage());
            }

            $token  = $this->scanner->getToken();
            $opcode = $token->opcode;

            $this->state->setActiveToken($opcode);
            $this->startLength = strlen($this->code) - $this->state->getCursor();

            if ($token->value !== null) {
                $this->value = $token->value;
            }

            $fed = $this->check($token);

            if ($opcode !== Opcode::IGNORE->value) {
                $this->previous = $opcode;
            }

            if ($fed !== null) {
                return $fed;
            }
        }
    }

    /**
     * The `%stack_overflow` message of parser.php.lemon (no file, no line).
     */
    public function stackOverflow(): Exception
    {
        return new Exception('Parsing failed, the template expression is nested too deeply');
    }

    /**
     * The `%syntax_error` message of parser.php.lemon, built from the state
     * left by the last scanned token.
     */
    public function syntaxError(): Exception
    {
        if ($this->startLength !== 0) {
            $active = (int) $this->state->getActiveToken();
            $name   = $active !== 0 ? Tokens::name($active) : 'UNKNOWN';
            $value  = $this->value !== null ? '(' . $this->value . ')' : '';

            return new Exception(
                'Syntax error, unexpected token ' . $name . $value
                . ' in ' . $this->file . ' on line ' . $this->line()
            );
        }

        $message = 'Syntax error, unexpected EOF in ' . $this->file;

        $ifBlocks = ($this->ifLevel + $this->oldIfLevel) & self::UINT_MASK;
        if ($ifBlocks === 1) {
            $message .= ", there is one 'if' block without close";
        } elseif ($ifBlocks > 1) {
            $message .= ", there are " . $ifBlocks . " 'if' blocks without close";
        }

        if ($this->forLevel === 1) {
            $message .= ", there is one 'for' block without close";
        } elseif ($this->forLevel > 1) {
            // cphalcon prints the if level here, not the for level
            $message .= ", there are " . $this->ifLevel . " 'for' blocks without close";
        }

        if ($this->switchLevel > 0) {
            $message .= ", there is a 'switch' block without 'endswitch'";
        }

        return new Exception($message);
    }

    /**
     * @throws Exception
     */
    private function assertBlockInChildTemplate(): void
    {
        if ($this->extendsMode && $this->blockLevel === 0) {
            throw $this->templateError('Child templates only may contain blocks');
        }
    }

    /**
     * The per-token switch of base.c: returns the token to feed to the
     * parser, or null when the token is not fed.
     *
     * @throws Exception
     */
    private function check(Token $token): ?Token
    {
        $opcode = Opcode::from($token->opcode);

        switch ($opcode) {
            case Opcode::IGNORE:
                return null;

            case Opcode::OPEN_EDELIMITER:
            case Opcode::SET:
                $this->assertBlockInChildTemplate();

                return $token;

            case Opcode::IF:
                $this->assertBlockInChildTemplate();
                $this->ifLevel    = $this->increment($this->ifLevel);
                $this->blockLevel = $this->increment($this->blockLevel);

                return $token;

            case Opcode::ELSE:
                if ($this->ifLevel === 0 && $this->forLevel > 0) {
                    return new Token(Opcode::ELSEFOR->value);
                }

                return $token;

            case Opcode::ELSEIF:
                if ($this->ifLevel === 0) {
                    throw $this->templateError('Unexpected ENDIF');
                }

                return $token;

            case Opcode::ENDIF:
                $this->blockLevel = $this->decrement($this->blockLevel);
                $this->ifLevel    = $this->decrement($this->ifLevel);

                return $token;

            case Opcode::FOR:
                $this->assertBlockInChildTemplate();
                $this->oldIfLevel = $this->ifLevel;
                $this->ifLevel    = 0;
                $this->forLevel   = $this->increment($this->forLevel);
                $this->blockLevel = $this->increment($this->blockLevel);

                return $token;

            case Opcode::ENDFOR:
                $this->blockLevel = $this->decrement($this->blockLevel);
                $this->forLevel   = $this->decrement($this->forLevel);
                $this->ifLevel    = $this->oldIfLevel;

                return $token;

            case Opcode::SWITCH:
                $this->assertBlockInChildTemplate();
                if ($this->switchLevel > 0) {
                    throw $this->templateError(
                        'A nested switch detected. There is no nested switch-case statements support'
                    );
                }
                $this->switchLevel = 1;
                $this->blockLevel  = $this->increment($this->blockLevel);

                return $token;

            case Opcode::CASE:
                if ($this->switchLevel === 0) {
                    throw $this->templateError('Unexpected CASE');
                }

                return $token;

            case Opcode::DEFAULT:
                if ($this->switchLevel !== 0 && $this->previous === Opcode::OPEN_DELIMITER->value) {
                    // the value lives until the parser consumes the clause keyword
                    return new Token(Opcode::DEFAULT->value, $token->value);
                }

                return new Token(Opcode::IDENTIFIER->value, $token->value);

            case Opcode::ENDSWITCH:
                if ($this->switchLevel === 0) {
                    throw $this->templateError('Unexpected ENDSWITCH');
                }
                $this->switchLevel = 0;
                $this->blockLevel  = $this->decrement($this->blockLevel);

                return $token;

            case Opcode::RAW_FRAGMENT:
                return $this->checkRawFragment($token);

            case Opcode::BLOCK:
                if ($this->blockLevel > 0) {
                    throw $this->templateError('Embedding blocks into other blocks is not supported');
                }
                $this->blockLevel = $this->increment($this->blockLevel);

                return $token;

            case Opcode::ENDBLOCK:
                $this->blockLevel = $this->decrement($this->blockLevel);

                return $token;

            case Opcode::MACRO:
                if ($this->macroLevel > 0) {
                    throw $this->templateError('Embedding macros into other macros is not allowed');
                }
                $this->macroLevel = $this->increment($this->macroLevel);

                return $token;

            case Opcode::ENDMACRO:
                $this->macroLevel = $this->decrement($this->macroLevel);

                return $token;

            case Opcode::EXTENDS:
                if ($this->state->getStatementPosition() !== 1) {
                    throw $this->templateError('Extends statement must be placed at the first line in the template');
                }
                $this->extendsMode = true;

                return $token;

            default:
                return $token;
        }
    }

    /**
     * @throws Exception
     */
    private function checkRawFragment(Token $token): ?Token
    {
        $fragment = (string) $token->value;

        if ($fragment === '') {
            // freed, not cleared, length 0: a later error prints "()"
            $this->value = '';

            return null;
        }

        $blank = strspn($fragment, self::BLANK) === strlen($fragment);

        if ($this->extendsMode && $this->blockLevel === 0) {
            if (!$blank) {
                throw $this->templateError('Child templates only may contain blocks');
            }
            // freed, not cleared: cphalcon would print heap garbage
            $this->value = null;

            return null;
        }

        if (!$blank) {
            $this->state->incrementStatementPosition();
        }

        return $token;
    }

    private function decrement(int $level): int
    {
        return ($level - 1) & self::UINT_MASK;
    }

    private function increment(int $level): int
    {
        return ($level + 1) & self::UINT_MASK;
    }

    /**
     * phvolt_scanner_error_msg. The offending byte has been consumed; the
     * message quotes what follows it - 16 bytes and "..." when more than 16
     * bytes were left before it, the whole rest otherwise. Like a C string,
     * the quote stops at a NUL byte.
     */
    private function scanningErrorMessage(): string
    {
        $rest = substr($this->code, $this->state->getCursor());
        $nul  = strpos($rest, "\0");
        if ($nul !== false) {
            $rest = substr($rest, 0, $nul);
        }

        if ($this->startLength > 16) {
            return sprintf(
                "Scanning error before '%s...' in %s on line %d",
                substr($rest, 0, 16),
                $this->file,
                $this->line()
            );
        }

        return sprintf("Scanning error before '%s' in %s on line %d", $rest, $this->file, $this->line());
    }

    /**
     * phvolt_create_error_msg: "<message> in <file> on line <line>", capped
     * like the C buffer (128 + strlen(file) bytes including the terminator).
     */
    private function templateError(string $message): Exception
    {
        $error = sprintf('%s in %s on line %d', $message, $this->file, $this->line());

        return new Exception(substr($error, 0, 127 + strlen($this->file)));
    }
}
