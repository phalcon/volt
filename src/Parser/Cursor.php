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
use Phalcon\Volt\Scanner\Token;

use function fwrite;
use function is_resource;

/**
 * The parser's view of the token stream: one token of lookahead plus an
 * emulation of the Lemon parser stack that cphalcon uses.
 *
 * cphalcon's parser is an LALR(1) automaton with a fixed 100-entry stack
 * (parser.c: VVSTACKDEPTH). The recursive-descent parser reproduces it by
 * calling shift() for every terminal Lemon would shift and reduce() exactly
 * once per grammar rule, at the point the rule's node is built:
 *
 *  - shift() pushes one entry and fails closed on overflow, exactly when
 *    Lemon's vv_shift() would ("nested too deeply");
 *  - reduce($n) pops the rule's $n right-hand-side entries and pushes the
 *    left-hand side; with no empty rules in the grammar this never grows the
 *    stack, so it never overflows.
 *
 * Lemon only reduces when the NEXT token has arrived, and its node builders
 * stamp the scanner's current line. shift() therefore fetches the next token
 * eagerly, and reduce() returns the line at that moment: the line of the token
 * that follows the construct. That is cphalcon's (sometimes surprising) line.
 */
final class Cursor
{
    /**
     * Lemon's stack holds VVSTACKDEPTH entries including the bottom sentinel.
     */
    private const MAX_DEPTH = 99;

    public Token $current;

    private int $depth = 0;

    /**
     * @param resource|null $trace
     */
    public function __construct(private readonly TokenStream $stream, private readonly mixed $trace = null)
    {
        $this->current = $stream->next();
    }

    public function atEnd(): bool
    {
        return $this->current->opcode === 0;
    }

    /**
     * Shifts the current token if it is $opcode; a syntax error otherwise.
     *
     * @throws Exception
     *
     * @phpstan-impure
     */
    public function expect(Opcode $opcode): Token
    {
        if (!$this->is($opcode)) {
            throw $this->stream->syntaxError();
        }

        return $this->shift();
    }

    public function is(Opcode $opcode): bool
    {
        return $this->current->opcode === $opcode->value;
    }

    /**
     * Reduces a grammar rule with $rhsLength right-hand-side symbols and
     * returns the line to stamp on its node.
     */
    public function reduce(int $rhsLength): int
    {
        $this->depth -= $rhsLength - 1;
        $this->log('reduce ' . $rhsLength);

        return $this->stream->line();
    }

    /**
     * Consumes the current token and fetches the next one.
     *
     * @throws Exception
     *
     * @phpstan-impure
     */
    public function shift(): Token
    {
        if ($this->depth >= self::MAX_DEPTH) {
            throw $this->stream->stackOverflow();
        }

        $this->depth++;
        $token = $this->current;
        $this->log('shift ' . $token->opcode . ($token->value !== null ? ' ' . $token->value : ''));

        $this->stream->consumed($token);
        $this->current = $this->stream->next();

        return $token;
    }

    /**
     * The syntax error for the current token.
     */
    public function unexpected(): Exception
    {
        return $this->stream->syntaxError();
    }

    private function log(string $message): void
    {
        if (is_resource($this->trace)) {
            fwrite($this->trace, $message . "\n");
        }
    }
}
