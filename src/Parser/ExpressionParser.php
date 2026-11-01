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

/**
 * Volt expressions by precedence climbing, resolving every shift/reduce choice
 * the way cphalcon's Lemon grammar does (parser.php.lemon).
 *
 * The precedence levels are copied from the grammar's declarations
 * (lines 22-36, later lines bind tighter):
 *
 *      3  ? :            left        9  + - ~        left
 *      4  ..             left       10  [            right
 *      5  and or         left       11  |            left
 *      6  in             left       12  not / !      right
 *      7  is == != < >   left       13  ++ --        left
 *         <= >= === !==             14  (            right
 *      8  * / %          left       15  .            left
 *
 * Note `* / %` binding LOOSER than `+ - ~`, `!` binding tighter than `[` and
 * `|`, and `not in` (level 12) binding much tighter than `in` (level 6). These
 * are cphalcon's behaviour and are reproduced, not corrected.
 *
 * A rule takes the precedence of its left-most terminal that has one (Lemon's
 * rule), so `a ** b` / `a // b` are level 8, `a not in b` and `!a` are level
 * 12, `-a` / `+a` are level 9 and the ternary is level 3. With a rule of level
 * p pending, a lookahead of level q is shifted iff q > p, or q == p and the
 * lookahead is right-associative; otherwise the pending rule is reduced first.
 * At the top of an expression nothing is pending and every operator shifts.
 */
final class ExpressionParser
{
    public function __construct(private readonly Cursor $cursor, private readonly Ast $ast)
    {
    }

    private static function isRightAssociative(int $opcode): bool
    {
        return $opcode === Opcode::SBRACKET_OPEN->value
            || $opcode === Opcode::NOT->value
            || $opcode === Opcode::PARENTHESES_OPEN->value;
    }

    /**
     * Precedence level of a token that can follow an expression; null for
     * every token that ends one (`,` `:` `)` `%}` ... - their declared
     * precedence never decides a conflict).
     */
    private static function level(int $opcode): ?int
    {
        return match ($opcode) {
            Opcode::QUESTION->value               => 3,
            Opcode::RANGE->value                  => 4,
            Opcode::AND->value, Opcode::OR->value => 5,
            Opcode::IN->value                     => 6,
            Opcode::IS->value,
            Opcode::EQUALS->value,
            Opcode::NOTEQUALS->value,
            Opcode::LESS->value,
            Opcode::GREATER->value,
            Opcode::GREATEREQUAL->value,
            Opcode::LESSEQUAL->value,
            Opcode::IDENTICAL->value,
            Opcode::NOTIDENTICAL->value                                   => 7,
            Opcode::DIV->value, Opcode::MUL->value, Opcode::MOD->value    => 8,
            Opcode::ADD->value, Opcode::SUB->value, Opcode::CONCAT->value => 9,
            Opcode::SBRACKET_OPEN->value                                  => 10,
            Opcode::PIPE->value                                           => 11,
            Opcode::NOT->value                                            => 12,
            Opcode::INCR->value, Opcode::DECR->value                      => 13,
            Opcode::PARENTHESES_OPEN->value                               => 14,
            Opcode::DOT->value                                            => 15,
            default                                                       => null,
        };
    }

    /**
     * Extends an already-reduced expression with the operators that follow it.
     *
     * @param array<string, mixed> $left
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function continue(array $left, ?int $pending): array
    {
        while ($this->shifts($pending)) {
            $left = $this->parseOperator($left);
        }

        return $left;
    }

    /**
     * expr, with a rule of precedence $pending waiting for it (null when
     * nothing is pending).
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function parse(?int $pending = null): array
    {
        return $this->continue($this->parsePrefix(), $pending);
    }

    /**
     * The expression of `{% call expr(args) %}`. Lemon prefers shifting there,
     * so a call written at the top of the expression that is directly followed
     * by `%}` belongs to the statement, not to an FCALL node: `{% call a.b(1) %}`
     * calls `a.b` with (1), while `{% call a + b(1) %}` is a syntax error.
     * Returns the call target and its arguments (null for `()`).
     *
     * @return array{0: array<string, mixed>, 1: list<array<string, mixed>>|null}
     *
     * @throws Exception
     */
    public function parseCallTarget(): array
    {
        $left = $this->parsePrefix();

        while (true) {
            if ($this->cursor->is(Opcode::PARENTHESES_OPEN)) {
                $this->cursor->shift();
                $arguments = $this->cursor->is(Opcode::PARENTHESES_CLOSE) ? null : $this->parseArguments();
                $this->cursor->expect(Opcode::PARENTHESES_CLOSE);

                if ($this->cursor->is(Opcode::CLOSE_DELIMITER)) {
                    return [$left, $arguments];
                }

                $line = $this->cursor->reduce($arguments === null ? 3 : 4);
                $left = $this->ast->functionCall($left, $arguments, $line);

                continue;
            }

            if (!$this->shifts(null)) {
                throw $this->cursor->unexpected();
            }

            $left = $this->parseOperator($left);
        }
    }

    /**
     * Parses the right operand of a binary rule and reduces it.
     *
     * @param array<string, mixed> $left
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function binary(Opcode $type, array $left, int $precedence, int $rhsLength): array
    {
        $right = $this->parse($precedence);

        return $this->ast->expr($type, $left, $right, null, $this->cursor->reduce($rhsLength));
    }

    /**
     * After `expr [`: an access `[expr]` or a slice `[a:b]` / `[:b]` / `[a:]`,
     * whose offsets can only be an integer or an identifier.
     *
     * @param array<string, mixed> $left
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseAccessOrSlice(array $left): array
    {
        if ($this->cursor->is(Opcode::COLON)) {
            // expr ::= expr [ : slice_offset ]
            $this->cursor->shift();
            $end = $this->parseSliceOffset();
            $this->cursor->expect(Opcode::SBRACKET_CLOSE);

            return $this->ast->slice($left, null, $end, $this->cursor->reduce(5));
        }

        $offset = $this->sliceOffsetOpcode();
        if ($offset !== null) {
            $token = $this->cursor->shift();

            if ($this->cursor->is(Opcode::COLON)) {
                $start = $this->ast->literal($offset, $token->value, $this->cursor->reduce(1));
                $this->cursor->shift();

                if ($this->cursor->is(Opcode::SBRACKET_CLOSE)) {
                    // expr ::= expr [ slice_offset : ]
                    $this->cursor->shift();

                    return $this->ast->slice($left, $start, null, $this->cursor->reduce(5));
                }

                // expr ::= expr [ slice_offset : slice_offset ]
                $end = $this->parseSliceOffset();
                $this->cursor->expect(Opcode::SBRACKET_CLOSE);

                return $this->ast->slice($left, $start, $end, $this->cursor->reduce(6));
            }

            // not a slice after all: the offset is the start of an expression
            $literal = $this->ast->literal($offset, $token->value, $this->cursor->reduce(1));
            $index   = $this->continue($literal, null);
        } else {
            $index = $this->parse();
        }

        // expr ::= expr [ expr ]
        $this->cursor->expect(Opcode::SBRACKET_CLOSE);

        return $this->ast->expr(Opcode::ARRAYACCESS, $left, $index, null, $this->cursor->reduce(4));
    }

    /**
     * argument_list ::= argument_list , argument_item | argument_item
     *
     * @return list<array<string, mixed>>
     *
     * @throws Exception
     */
    private function parseArguments(): array
    {
        return $this->parseItems();
    }

    /**
     * `[ ... ]` and `{ ... }` both build an ARRAY node.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseArray(Opcode $close): array
    {
        $this->cursor->shift();

        if ($this->cursor->is($close)) {
            // expr ::= [ ] | { }
            $this->cursor->shift();

            return $this->ast->expr(Opcode::ARRAY, null, null, null, $this->cursor->reduce(2));
        }

        // expr ::= [ array_list ] | { array_list }
        $items = $this->parseItems();
        $this->cursor->expect($close);

        return $this->ast->expr(Opcode::ARRAY, $items, null, null, $this->cursor->reduce(3));
    }

    /**
     * array_item / argument_item ::= STRING : expr | expr
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseItem(): array
    {
        if (!$this->cursor->is(Opcode::STRING)) {
            $expr = $this->parse();

            return $this->ast->namedItem(null, $expr, $this->cursor->reduce(1));
        }

        $token = $this->cursor->shift();

        if ($this->cursor->is(Opcode::COLON)) {
            $this->cursor->shift();
            $expr = $this->parse();

            return $this->ast->namedItem($token->value, $expr, $this->cursor->reduce(3));
        }

        // not a key after all: the string starts an expression
        $literal = $this->ast->literal(Opcode::STRING, $token->value, $this->cursor->reduce(1));
        $expr    = $this->continue($literal, null);

        return $this->ast->namedItem(null, $expr, $this->cursor->reduce(1));
    }

    /**
     * array_list / argument_list (identical shapes), as a loop: the
     * left-recursive list keeps one stack entry however long it gets.
     *
     * @return list<array<string, mixed>>
     *
     * @throws Exception
     */
    private function parseItems(): array
    {
        $items = [$this->parseItem()];
        $this->cursor->reduce(1);

        while ($this->cursor->is(Opcode::COMMA)) {
            $this->cursor->shift();
            $items[] = $this->parseItem();
            $this->cursor->reduce(3);
        }

        return $items;
    }

    /**
     * One infix/postfix operator applied to $left.
     *
     * @param array<string, mixed> $left
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseOperator(array $left): array
    {
        $operator = Opcode::from($this->cursor->current->opcode);
        $this->cursor->shift();

        switch ($operator) {
            case Opcode::SBRACKET_OPEN:
                return $this->parseAccessOrSlice($left);

            case Opcode::PARENTHESES_OPEN:
                $arguments = $this->cursor->is(Opcode::PARENTHESES_CLOSE) ? null : $this->parseArguments();
                $this->cursor->expect(Opcode::PARENTHESES_CLOSE);

                // function_call ::= expr ( [argument_list] )
                return $this->ast->functionCall($left, $arguments, $this->cursor->reduce($arguments === null ? 3 : 4));

            case Opcode::QUESTION:
                // expr ::= expr ? expr : expr
                $then = $this->parse();
                $this->cursor->expect(Opcode::COLON);
                $else = $this->parse(3);

                return $this->ast->expr(Opcode::TERNARY, $then, $else, $left, $this->cursor->reduce(5));

            case Opcode::INCR:
            case Opcode::DECR:
                return $this->ast->expr($operator, $left, null, null, $this->cursor->reduce(2));

            case Opcode::IS:
            case Opcode::NOTEQUALS:
                $test = $this->testOpcode($operator === Opcode::IS);
                if ($test !== null) {
                    // expr ::= expr IS|NOTEQUALS DEFINED|EMPTY|EVEN|ODD|NUMERIC|SCALAR|ITERABLE
                    $this->cursor->shift();

                    return $this->ast->expr($test, $left, null, null, $this->cursor->reduce(3));
                }

                return $this->binary($operator, $left, 7, 3);

            case Opcode::MUL:
                if ($this->cursor->is(Opcode::MUL)) {
                    // expr ::= expr TIMES TIMES expr
                    $this->cursor->shift();

                    return $this->binary(Opcode::POW, $left, 8, 4);
                }

                return $this->binary(Opcode::MUL, $left, 8, 3);

            case Opcode::DIV:
                if ($this->cursor->is(Opcode::DIV)) {
                    // expr ::= expr DIVIDE DIVIDE expr (builds a MOD node)
                    $this->cursor->shift();

                    return $this->binary(Opcode::MOD, $left, 8, 4);
                }

                return $this->binary(Opcode::DIV, $left, 8, 3);

            case Opcode::NOT:
                // expr ::= expr NOT IN expr
                $this->cursor->expect(Opcode::IN);

                return $this->binary(Opcode::NOT_IN, $left, 12, 4);

            default:
                // expr ::= expr OP expr
                return $this->binary($operator, $left, (int) self::level($operator->value), 3);
        }
    }

    /**
     * A primary expression or a prefix operator.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parsePrefix(): array
    {
        $opcode = $this->cursor->current->opcode;

        switch ($opcode) {
            case Opcode::SUB->value:
                // expr ::= MINUS expr
                $this->cursor->shift();
                $operand = $this->parse(9);

                return $this->ast->expr(Opcode::MINUS, null, $operand, null, $this->cursor->reduce(2));

            case Opcode::ADD->value:
                // expr ::= PLUS expr
                $this->cursor->shift();
                $operand = $this->parse(9);

                return $this->ast->expr(Opcode::PLUS, null, $operand, null, $this->cursor->reduce(2));

            case Opcode::NOT->value:
                // expr ::= NOT expr
                $this->cursor->shift();
                $operand = $this->parse(12);

                return $this->ast->expr(Opcode::NOT, null, $operand, null, $this->cursor->reduce(2));

            case Opcode::PARENTHESES_OPEN->value:
                // expr ::= ( expr )
                $this->cursor->shift();
                $inner = $this->parse();
                $this->cursor->expect(Opcode::PARENTHESES_CLOSE);

                return $this->ast->expr(Opcode::ENCLOSED, $inner, null, null, $this->cursor->reduce(3));

            case Opcode::SBRACKET_OPEN->value:
                return $this->parseArray(Opcode::SBRACKET_CLOSE);

            case Opcode::CBRACKET_OPEN->value:
                return $this->parseArray(Opcode::CBRACKET_CLOSE);

            case Opcode::IDENTIFIER->value:
            case Opcode::INTEGER->value:
            case Opcode::STRING->value:
            case Opcode::DOUBLE->value:
            case Opcode::NULL->value:
            case Opcode::FALSE->value:
            case Opcode::TRUE->value:
                // expr ::= IDENTIFIER | INTEGER | STRING | DOUBLE | NULL | FALSE | TRUE
                $token = $this->cursor->shift();

                return $this->ast->literal(Opcode::from($opcode), $token->value, $this->cursor->reduce(1));

            default:
                throw $this->cursor->unexpected();
        }
    }

    /**
     * slice_offset ::= INTEGER | IDENTIFIER
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseSliceOffset(): array
    {
        $offset = $this->sliceOffsetOpcode();
        if ($offset === null) {
            throw $this->cursor->unexpected();
        }

        $token = $this->cursor->shift();

        return $this->ast->literal($offset, $token->value, $this->cursor->reduce(1));
    }

    /**
     * Whether the current token continues the expression with $pending
     * waiting for it.
     */
    private function shifts(?int $pending): bool
    {
        $opcode = $this->cursor->current->opcode;
        $level  = self::level($opcode);

        if ($level === null) {
            return false;
        }

        return $pending === null
            || $level > $pending
            || ($level === $pending && self::isRightAssociative($opcode));
    }

    private function sliceOffsetOpcode(): ?Opcode
    {
        return match ($this->cursor->current->opcode) {
            Opcode::INTEGER->value    => Opcode::INTEGER,
            Opcode::IDENTIFIER->value => Opcode::IDENTIFIER,
            default                   => null,
        };
    }

    /**
     * The node type for `is <test>` / `is not <test>` when the current token
     * is one of the test keywords; null when it starts an operand instead.
     */
    private function testOpcode(bool $positive): ?Opcode
    {
        return match ($this->cursor->current->opcode) {
            Opcode::DEFINED->value  => $positive ? Opcode::ISSET : Opcode::NOT_ISSET,
            Opcode::EMPTY->value    => $positive ? Opcode::ISEMPTY : Opcode::NOT_ISEMPTY,
            Opcode::EVEN->value     => $positive ? Opcode::ISEVEN : Opcode::NOT_ISEVEN,
            Opcode::ODD->value      => $positive ? Opcode::ISODD : Opcode::NOT_ISODD,
            Opcode::NUMERIC->value  => $positive ? Opcode::ISNUMERIC : Opcode::NOT_ISNUMERIC,
            Opcode::SCALAR->value   => $positive ? Opcode::ISSCALAR : Opcode::NOT_ISSCALAR,
            Opcode::ITERABLE->value => $positive ? Opcode::ISITERABLE : Opcode::NOT_ISITERABLE,
            default                 => null,
        };
    }
}
