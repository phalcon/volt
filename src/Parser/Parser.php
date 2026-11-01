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

use function fclose;
use function fopen;

/**
 * Parses Volt templates into cphalcon's intermediate representation.
 *
 * A hand-written replacement for the Lemon parser that cphalcon generates from
 * ext/phalcon/mvc/view/engine/volt/parser.php.lemon, producing the same output
 * for every input: the same AST (key order and types included), the same line
 * numbers, the same error messages on the same tokens, and the same "nested
 * too deeply" failure at the same depth. Behaviour is pinned against
 * ext-phalcon by the parity goldens in tests/fixtures/parity.
 *
 * Statements are parsed here by recursive descent - one method per grammar
 * rule group, each quoting its productions - and expressions by
 * ExpressionParser. Every method mirrors the LALR parser exactly: it shifts
 * the terminals Lemon shifts and reduces each rule once, with its
 * right-hand-side length, when the rule's node is built (see Cursor).
 *
 * The one deliberate difference from cphalcon is the removed
 * `{% cache %}` statement: `cache` and `endcache` are plain identifiers.
 */
class Parser
{
    private Ast $ast;

    private Cursor $cursor;

    private bool $debug = false;

    private string $debugFile = 'volt.txt';

    private ExpressionParser $expressions;

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws Exception
     */
    public function parse(string $code, string $templatePath = 'eval code'): array
    {
        if ($code === '') {
            return [];
        }

        $trace = $this->debug ? fopen($this->debugFile, 'w+') : null;

        try {
            $this->ast         = new Ast($templatePath);
            $this->cursor      = new Cursor(new TokenStream($code, $templatePath), $trace ?: null);
            $this->expressions = new ExpressionParser($this->cursor, $this->ast);

            return $this->parseProgram();
        } finally {
            if ($trace) {
                fclose($trace);
            }
        }
    }

    /**
     * Writes a shift/reduce trace of the next parse to the debug file.
     *
     * @deprecated Kept for backwards compatibility; the trace format is not stable.
     */
    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @deprecated Kept for backwards compatibility; see setDebug().
     */
    public function setDebugFile(string $debugFile): static
    {
        $this->debugFile = $debugFile;

        return $this;
    }

    /**
     * After parseStatementList(): the expected terminator keyword, then `%}`.
     * When the list stopped at anything else, that token is the syntax error.
     *
     * @throws Exception
     */
    private function closeList(Opcode $keyword, ?Opcode $terminator): void
    {
        if ($terminator !== $keyword) {
            throw $this->cursor->unexpected();
        }

        $this->closeTag();
    }

    /**
     * Shifts the current terminator keyword and the `%}` after it.
     *
     * @throws Exception
     */
    private function closeTag(): void
    {
        $this->cursor->shift();
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);
    }

    /**
     * `{% KEYWORD %}` in full, when no statement may come first.
     *
     * @throws Exception
     */
    private function expectEndTag(Opcode $keyword): void
    {
        $this->cursor->expect(Opcode::OPEN_DELIMITER);
        $this->cursor->expect($keyword);
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);
    }

    /**
     * assignable_expr ::= IDENTIFIER
     *                   | assignable_expr [ expr ]
     *                   | assignable_expr . assignable_expr
     *
     * `.` (level 15, left) always reduces before a following `[` or `.`, so
     * its right-hand side is a single identifier: `a.b.c[0]` is
     * `((a.b).c)[0]`.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseAssignable(): array
    {
        $token    = $this->cursor->expect(Opcode::IDENTIFIER);
        $variable = $this->ast->literal(Opcode::IDENTIFIER, $token->value, $this->cursor->reduce(1));

        while (true) {
            if ($this->cursor->is(Opcode::SBRACKET_OPEN)) {
                $this->cursor->shift();
                $index = $this->expressions->parse();
                $this->cursor->expect(Opcode::SBRACKET_CLOSE);
                $variable = $this->ast->expr(Opcode::ARRAYACCESS, $variable, $index, null, $this->cursor->reduce(4));
            } elseif ($this->cursor->is(Opcode::DOT)) {
                $this->cursor->shift();
                $token    = $this->cursor->expect(Opcode::IDENTIFIER);
                $property = $this->ast->literal(Opcode::IDENTIFIER, $token->value, $this->cursor->reduce(1));
                $variable = $this->ast->expr(Opcode::DOT, $variable, $property, null, $this->cursor->reduce(3));
            } else {
                return $variable;
            }
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseAssignment(): array
    {
        $variable = $this->parseAssignable();

        $operator = match ($this->cursor->current->opcode) {
            Opcode::ASSIGN->value     => Opcode::ASSIGN,
            Opcode::ADD_ASSIGN->value => Opcode::ADD_ASSIGN,
            Opcode::SUB_ASSIGN->value => Opcode::SUB_ASSIGN,
            Opcode::MUL_ASSIGN->value => Opcode::MUL_ASSIGN,
            Opcode::DIV_ASSIGN->value => Opcode::DIV_ASSIGN,
            default                   => throw $this->cursor->unexpected(),
        };

        $this->cursor->shift();
        $expr = $this->expressions->parse();

        return $this->ast->setAssignment($variable, $operator, $expr, $this->cursor->reduce(3));
    }

    /**
     * autoescape_statement ::= {% AUTOESCAPE FALSE|TRUE %} statement_list {% ENDAUTOESCAPE %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseAutoescape(): array
    {
        $this->cursor->shift();

        if (!$this->cursor->is(Opcode::TRUE) && !$this->cursor->is(Opcode::FALSE)) {
            throw $this->cursor->unexpected();
        }

        $enable = $this->cursor->shift()->opcode === Opcode::TRUE->value;
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);
        $statements = $this->parseBody(Opcode::ENDAUTOESCAPE);

        return $this->ast->autoescapeStatement($enable, $statements, $this->cursor->reduce(8));
    }

    /**
     * break_statement    ::= {% BREAK %}
     * continue_statement ::= {% CONTINUE %}
     * elsefor_statement  ::= {% ELSEFOR %}
     * case_clause        ::= {% DEFAULT %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseBare(Opcode $type): array
    {
        $this->cursor->shift();
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);
        $line = $this->cursor->reduce(3);

        return $type === Opcode::DEFAULT
            ? $this->ast->caseClause(null, $line)
            : $this->ast->bareStatement($type, $line);
    }

    /**
     * block_statement ::= {% BLOCK IDENTIFIER %} statement_list {% ENDBLOCK %}
     *                   | {% BLOCK IDENTIFIER %} {% ENDBLOCK %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseBlock(): array
    {
        $this->cursor->shift();
        $name = (string) $this->cursor->expect(Opcode::IDENTIFIER)->value;
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);

        [$statements, $terminator] = $this->parseStatementList([Opcode::ENDBLOCK], [Opcode::ENDBLOCK]);
        $this->closeList(Opcode::ENDBLOCK, $terminator);

        return $this->ast->blockStatement($name, $statements, $this->cursor->reduce($statements === null ? 7 : 8));
    }

    /**
     * A body that must contain at least one statement, then `{% END... %}`.
     *
     * @return list<array<string, mixed>>
     *
     * @throws Exception
     */
    private function parseBody(Opcode $end): array
    {
        [$statements, $terminator] = $this->parseStatementList([], [$end]);

        if ($statements === null) {
            throw $this->cursor->unexpected();
        }

        $this->closeList($end, $terminator);

        return $statements;
    }

    /**
     * macro_call_statement ::= {% CALL expr ( argument_list ) %} statement_list {% ENDCALL %}
     *                        | {% CALL expr ( ) %} {% ENDCALL %}
     *
     * The call with arguments needs a body and the call without arguments
     * must not have one - those are the only two rules.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseCall(): array
    {
        $this->cursor->shift();
        [$name, $arguments] = $this->expressions->parseCallTarget();
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);

        if ($arguments === null) {
            $this->expectEndTag(Opcode::ENDCALL);

            return $this->ast->macroCallStatement($name, null, null, $this->cursor->reduce(9));
        }

        $caller = $this->parseBody(Opcode::ENDCALL);

        return $this->ast->macroCallStatement($name, $arguments, $caller, $this->cursor->reduce(11));
    }

    /**
     * case_clause ::= {% CASE expr %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseCase(): array
    {
        $expr = $this->parseKeywordExpr();

        return $this->ast->caseClause($expr, $this->cursor->reduce(4));
    }

    /**
     * echo_statement ::= {{ expr }}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseEcho(): array
    {
        $this->cursor->shift();
        $expr = $this->expressions->parse();
        $this->cursor->expect(Opcode::CLOSE_EDELIMITER);

        return $this->ast->exprStatement(Opcode::ECHO, $expr, $this->cursor->reduce(3));
    }

    /**
     * elseif_statement ::= {% ELSEIF expr %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseElseIf(): array
    {
        $expr = $this->parseKeywordExpr();

        return $this->ast->elseIfStatement($expr, $this->cursor->reduce(4));
    }

    /**
     * empty_statement ::= {% %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseEmpty(): array
    {
        $this->cursor->shift();

        return $this->ast->bareStatement(Opcode::EMPTY_STATEMENT, $this->cursor->reduce(2));
    }

    /**
     * do_statement     ::= {% DO expr %}
     * return_statement ::= {% RETURN expr %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseExprStatement(Opcode $type): array
    {
        $expr = $this->parseKeywordExpr();

        return $this->ast->exprStatement($type, $expr, $this->cursor->reduce(4));
    }

    /**
     * extends_statement ::= {% EXTENDS expr %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseExtends(): array
    {
        $path = $this->parseKeywordExpr();

        return $this->ast->extendsStatement($path, $this->cursor->reduce(4));
    }

    /**
     * for_statement ::= {% FOR IDENTIFIER IN expr %} statement_list {% ENDFOR %}
     *                 | {% FOR IDENTIFIER IN expr IF expr %} statement_list {% ENDFOR %}
     *                 | {% FOR IDENTIFIER , IDENTIFIER IN expr %} statement_list {% ENDFOR %}
     *                 | {% FOR IDENTIFIER , IDENTIFIER IN expr IF expr %} statement_list {% ENDFOR %}
     *
     * In the key/value forms the first identifier is the key.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseFor(): array
    {
        $this->cursor->shift();
        $variable = (string) $this->cursor->expect(Opcode::IDENTIFIER)->value;
        $key      = null;
        $length   = 10;

        if ($this->cursor->is(Opcode::COMMA)) {
            $this->cursor->shift();
            $key      = $variable;
            $variable = (string) $this->cursor->expect(Opcode::IDENTIFIER)->value;
            $length  += 2;
        }

        $this->cursor->expect(Opcode::IN);
        $expr   = $this->expressions->parse();
        $ifExpr = null;

        if ($this->cursor->is(Opcode::IF)) {
            $this->cursor->shift();
            $ifExpr  = $this->expressions->parse();
            $length += 2;
        }

        $this->cursor->expect(Opcode::CLOSE_DELIMITER);
        $statements = $this->parseBody(Opcode::ENDFOR);

        return $this->ast->forStatement($variable, $key, $expr, $ifExpr, $statements, $this->cursor->reduce($length));
    }

    /**
     * if_statement ::= {% IF expr %} statement_list {% ENDIF %}
     *                | {% IF expr %} {% ENDIF %}
     *                | {% IF expr %} statement_list {% ELSE %} statement_list {% ENDIF %}
     *                | {% IF expr %} statement_list {% ELSE %} {% ENDIF %}
     *                | {% IF expr %} {% ELSE %} {% ENDIF %}
     *
     * There is no rule for an empty true branch with a non-empty else.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseIf(): array
    {
        $expr = $this->parseKeywordExpr();

        [$true, $terminator] = $this->parseStatementList([Opcode::ENDIF, Opcode::ELSE], [Opcode::ENDIF, Opcode::ELSE]);

        if ($terminator === null) {
            throw $this->cursor->unexpected();
        }

        $this->closeTag();

        if ($terminator === Opcode::ENDIF) {
            return $this->ast->ifStatement($expr, $true, null, $this->cursor->reduce($true === null ? 7 : 8));
        }

        if ($true === null) {
            $this->expectEndTag(Opcode::ENDIF);

            return $this->ast->ifStatement($expr, null, null, $this->cursor->reduce(10));
        }

        [$false, $terminator] = $this->parseStatementList([Opcode::ENDIF], [Opcode::ENDIF]);
        $this->closeList(Opcode::ENDIF, $terminator);

        return $this->ast->ifStatement($expr, $true, $false, $this->cursor->reduce($false === null ? 11 : 12));
    }

    /**
     * include_statement ::= {% INCLUDE expr %} | {% INCLUDE expr WITH expr %}
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseInclude(): array
    {
        $this->cursor->shift();
        $path   = $this->expressions->parse();
        $params = null;

        if ($this->cursor->is(Opcode::WITH)) {
            $this->cursor->shift();
            $params = $this->expressions->parse();
        }

        $this->cursor->expect(Opcode::CLOSE_DELIMITER);

        return $this->ast->includeStatement($path, $params, $this->cursor->reduce($params === null ? 4 : 6));
    }

    /**
     * Shifts the statement keyword, then parses `expr %}`.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseKeywordExpr(): array
    {
        $this->cursor->shift();
        $expr = $this->expressions->parse();
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);

        return $expr;
    }

    /**
     * macro_statement  ::= {% MACRO IDENTIFIER ( ) %} statement_list {% ENDMACRO %}
     *                    | {% MACRO IDENTIFIER ( macro_parameters ) %} statement_list {% ENDMACRO %}
     * macro_parameters ::= macro_parameters , macro_parameter | macro_parameter
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseMacro(): array
    {
        $this->cursor->shift();
        $name = (string) $this->cursor->expect(Opcode::IDENTIFIER)->value;
        $this->cursor->expect(Opcode::PARENTHESES_OPEN);

        $parameters = null;
        if (!$this->cursor->is(Opcode::PARENTHESES_CLOSE)) {
            $parameters = [$this->parseMacroParameter()];
            $this->cursor->reduce(1);

            while ($this->cursor->is(Opcode::COMMA)) {
                $this->cursor->shift();
                $parameters[] = $this->parseMacroParameter();
                $this->cursor->reduce(3);
            }
        }

        $this->cursor->expect(Opcode::PARENTHESES_CLOSE);
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);
        $statements = $this->parseBody(Opcode::ENDMACRO);

        return $this->ast->macroStatement(
            $name,
            $parameters,
            $statements,
            $this->cursor->reduce($parameters === null ? 10 : 11)
        );
    }

    /**
     * macro_parameter         ::= IDENTIFIER | IDENTIFIER = macro_parameter_default
     * macro_parameter_default ::= INTEGER | STRING | DOUBLE | NULL | FALSE | TRUE
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseMacroParameter(): array
    {
        $variable = (string) $this->cursor->expect(Opcode::IDENTIFIER)->value;

        if (!$this->cursor->is(Opcode::ASSIGN)) {
            return $this->ast->macroParameter($variable, null, $this->cursor->reduce(1));
        }

        $this->cursor->shift();

        $type = match ($this->cursor->current->opcode) {
            Opcode::INTEGER->value => Opcode::INTEGER,
            Opcode::STRING->value  => Opcode::STRING,
            Opcode::DOUBLE->value  => Opcode::DOUBLE,
            Opcode::NULL->value    => Opcode::NULL,
            Opcode::FALSE->value   => Opcode::FALSE,
            Opcode::TRUE->value    => Opcode::TRUE,
            default                => throw $this->cursor->unexpected(),
        };

        $token   = $this->cursor->shift();
        $default = $this->ast->literal($type, $token->value, $this->cursor->reduce(1));

        return $this->ast->macroParameter($variable, $default, $this->cursor->reduce(3));
    }

    /**
     * program ::= volt_language ::= statement_list
     *
     * A template that yields no token at all (only comments, say) is an empty
     * program, as in cphalcon.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws Exception
     */
    private function parseProgram(): array
    {
        if ($this->cursor->atEnd()) {
            return [];
        }

        [$statements] = $this->parseStatementList([], []);

        if ($statements === null || !$this->cursor->atEnd()) {
            throw $this->cursor->unexpected();
        }

        return $statements;
    }

    /**
     * raw_fragment ::= RAW_FRAGMENT
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseRawFragment(): array
    {
        $token = $this->cursor->shift();

        return $this->ast->literal(Opcode::RAW_FRAGMENT, $token->value, $this->cursor->reduce(1));
    }

    /**
     * set_statement   ::= {% SET set_assignments %}
     * set_assignments ::= set_assignments , set_assignment | set_assignment
     * set_assignment  ::= assignable_expr (= | += | -= | *= | /=) expr
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseSet(): array
    {
        $this->cursor->shift();

        $assignments = [$this->parseAssignment()];
        $this->cursor->reduce(1);

        while ($this->cursor->is(Opcode::COMMA)) {
            $this->cursor->shift();
            $assignments[] = $this->parseAssignment();
            $this->cursor->reduce(3);
        }

        $this->cursor->expect(Opcode::CLOSE_DELIMITER);
        $this->cursor->reduce(4);

        return $this->ast->setStatement($assignments);
    }

    /**
     * statement_list ::= statement_list statement | statement
     *
     * Parses statements until `{%` is followed by one of the terminators of the
     * enclosing rule; that `{%` has then been shifted and the terminator
     * keyword is the current token. $whenEmpty / $whenNotEmpty list the
     * terminators the enclosing rule accepts before and after the first
     * statement (the grammar has distinct rules for empty bodies).
     *
     * Returns at any other token that cannot start a statement, leaving the
     * caller to report it.
     *
     * @param list<Opcode> $whenEmpty
     * @param list<Opcode> $whenNotEmpty
     *
     * Returns the statements (null when there were none) and the terminator.
     *
     * @return array{0: list<array<string, mixed>>|null, 1: Opcode|null}
     *
     * @throws Exception
     *
     * @phpstan-impure
     */
    private function parseStatementList(array $whenEmpty, array $whenNotEmpty): array
    {
        $statements = null;

        while (true) {
            if ($this->cursor->is(Opcode::RAW_FRAGMENT)) {
                $statement = $this->parseRawFragment();
            } elseif ($this->cursor->is(Opcode::OPEN_EDELIMITER)) {
                $statement = $this->parseEcho();
            } elseif ($this->cursor->is(Opcode::OPEN_DELIMITER)) {
                $this->cursor->shift();

                $terminators = $statements === null ? $whenEmpty : $whenNotEmpty;
                foreach ($terminators as $terminator) {
                    if ($this->cursor->is($terminator)) {
                        return [$statements, $terminator];
                    }
                }

                $statement = $this->parseTag();
            } else {
                return [$statements, null];
            }

            if ($statements === null) {
                $statements = [$statement];
                $this->cursor->reduce(1);
            } else {
                $statements[] = $statement;
                $this->cursor->reduce(2);
            }
        }
    }

    /**
     * switch_statement ::= {% SWITCH expr %} statement_list {% ENDSWITCH %}
     *                    | {% SWITCH expr %} {% ENDSWITCH %}
     *
     * Case clauses are ordinary statements of the list.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseSwitch(): array
    {
        $expr = $this->parseKeywordExpr();

        [$clauses, $terminator] = $this->parseStatementList([Opcode::ENDSWITCH], [Opcode::ENDSWITCH]);
        $this->closeList(Opcode::ENDSWITCH, $terminator);

        return $this->ast->switchStatement($expr, $clauses, $this->cursor->reduce($clauses === null ? 7 : 8));
    }

    /**
     * A statement after its `{%` (already shifted).
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseTag(): array
    {
        return match ($this->cursor->current->opcode) {
            Opcode::IF->value              => $this->parseIf(),
            Opcode::ELSEIF->value          => $this->parseElseIf(),
            Opcode::ELSEFOR->value         => $this->parseBare(Opcode::ELSEFOR),
            Opcode::FOR->value             => $this->parseFor(),
            Opcode::SWITCH->value          => $this->parseSwitch(),
            Opcode::CASE->value            => $this->parseCase(),
            Opcode::DEFAULT->value         => $this->parseBare(Opcode::DEFAULT),
            Opcode::SET->value             => $this->parseSet(),
            Opcode::BLOCK->value           => $this->parseBlock(),
            Opcode::EXTENDS->value         => $this->parseExtends(),
            Opcode::INCLUDE->value         => $this->parseInclude(),
            Opcode::DO->value              => $this->parseExprStatement(Opcode::DO),
            Opcode::RETURN->value          => $this->parseExprStatement(Opcode::RETURN),
            Opcode::AUTOESCAPE->value      => $this->parseAutoescape(),
            Opcode::VERBATIM->value        => $this->parseVerbatim(),
            Opcode::BREAK->value           => $this->parseBare(Opcode::BREAK),
            Opcode::CONTINUE->value        => $this->parseBare(Opcode::CONTINUE),
            Opcode::MACRO->value           => $this->parseMacro(),
            Opcode::CALL->value            => $this->parseCall(),
            Opcode::CLOSE_DELIMITER->value => $this->parseEmpty(),
            default                        => throw $this->cursor->unexpected(),
        };
    }

    /**
     * verbatim_statement ::= {% VERBATIM %} raw_fragment {% ENDVERBATIM %}
     *                      | {% VERBATIM %} {% ENDVERBATIM %}
     *
     * The statement IS its raw fragment; an empty block is a RAW_FRAGMENT
     * node without a value.
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseVerbatim(): array
    {
        $this->cursor->shift();
        $this->cursor->expect(Opcode::CLOSE_DELIMITER);

        if ($this->cursor->is(Opcode::RAW_FRAGMENT)) {
            $fragment = $this->parseRawFragment();
            $this->expectEndTag(Opcode::ENDVERBATIM);
            $this->cursor->reduce(7);

            return $fragment;
        }

        $this->expectEndTag(Opcode::ENDVERBATIM);

        return $this->ast->literal(Opcode::RAW_FRAGMENT, null, $this->cursor->reduce(6));
    }
}
