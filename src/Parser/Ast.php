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

/**
 * Builds the intermediate representation (nested arrays) exactly as cphalcon's
 * `phvolt_ret_*` helpers do (ext/phalcon/mvc/view/engine/volt/parser.php.inc.h).
 *
 * Two rules are part of the output format and are what `assertSame()` checks:
 *
 *  - keys appear in the order the C helpers call `add_assoc_*`;
 *  - an optional key is omitted when absent, never set to null.
 *
 * Every node except the set statement ends with `file` and `line`; the line is
 * the one Lemon stamps when it reduces the rule (see Cursor::reduce()).
 */
final class Ast
{
    public function __construct(private readonly string $file)
    {
    }

    /**
     * @param list<array<string, mixed>> $blockStatements
     *
     * @return array<string, mixed>
     */
    public function autoescapeStatement(bool $enable, array $blockStatements, int $line): array
    {
        return $this->locate(
            [
                'type'             => Opcode::AUTOESCAPE->value,
                'enable'           => $enable ? 1 : 0,
                'block_statements' => $blockStatements,
            ],
            $line
        );
    }

    /**
     * Statements with nothing but a type: break, continue, `{% %}`.
     *
     * @return array<string, mixed>
     */
    public function bareStatement(Opcode $type, int $line): array
    {
        return $this->locate(['type' => $type->value], $line);
    }

    /**
     * @param list<array<string, mixed>>|null $blockStatements
     *
     * @return array<string, mixed>
     */
    public function blockStatement(string $name, ?array $blockStatements, int $line): array
    {
        $node = ['type' => Opcode::BLOCK->value, 'name' => $name];
        if ($blockStatements !== null) {
            $node['block_statements'] = $blockStatements;
        }

        return $this->locate($node, $line);
    }

    /**
     * phvolt_ret_case_clause: `{% case expr %}`, or `{% default %}` when there
     * is no expression.
     *
     * @param array<string, mixed>|null $expr
     *
     * @return array<string, mixed>
     */
    public function caseClause(?array $expr, int $line): array
    {
        $node = $expr === null
            ? ['type' => Opcode::DEFAULT->value]
            : ['type' => Opcode::CASE->value, 'expr' => $expr];

        return $this->locate($node, $line);
    }

    /**
     * @return array<string, mixed>
     */
    public function elseForStatement(int $line): array
    {
        return $this->locate(['type' => Opcode::ELSEFOR->value], $line);
    }

    /**
     * @param array<string, mixed> $expr
     *
     * @return array<string, mixed>
     */
    public function elseIfStatement(array $expr, int $line): array
    {
        return $this->locate(['type' => Opcode::ELSEIF->value, 'expr' => $expr], $line);
    }

    /**
     * phvolt_ret_expr: operators and other expression nodes. For a ternary,
     * `ternary` holds the condition, `left` the true and `right` the false
     * branch.
     *
     * @param array<array-key, mixed>|null $left
     * @param array<array-key, mixed>|null $right
     * @param array<array-key, mixed>|null $ternary
     *
     * @return array<string, mixed>
     */
    public function expr(Opcode $type, ?array $left, ?array $right, ?array $ternary, int $line): array
    {
        $node = ['type' => $type->value];
        if ($ternary !== null) {
            $node['ternary'] = $ternary;
        }
        if ($left !== null) {
            $node['left'] = $left;
        }
        if ($right !== null) {
            $node['right'] = $right;
        }

        return $this->locate($node, $line);
    }

    /**
     * Statements made of a type and one expression: echo, do, return.
     *
     * @param array<string, mixed> $expr
     *
     * @return array<string, mixed>
     */
    public function exprStatement(Opcode $type, array $expr, int $line): array
    {
        return $this->locate(['type' => $type->value, 'expr' => $expr], $line);
    }

    /**
     * @param array<string, mixed> $path
     *
     * @return array<string, mixed>
     */
    public function extendsStatement(array $path, int $line): array
    {
        return $this->locate(['type' => Opcode::EXTENDS->value, 'path' => $path], $line);
    }

    /**
     * @param array<string, mixed>       $expr
     * @param array<string, mixed>|null  $ifExpr
     * @param list<array<string, mixed>> $blockStatements
     *
     * @return array<string, mixed>
     */
    public function forStatement(
        string $variable,
        ?string $key,
        array $expr,
        ?array $ifExpr,
        array $blockStatements,
        int $line
    ): array {
        $node = ['type' => Opcode::FOR->value, 'variable' => $variable];
        if ($key !== null) {
            $node['key'] = $key;
        }
        $node['expr'] = $expr;
        if ($ifExpr !== null) {
            $node['if_expr'] = $ifExpr;
        }
        $node['block_statements'] = $blockStatements;

        return $this->locate($node, $line);
    }

    /**
     * @param array<string, mixed>            $name
     * @param list<array<string, mixed>>|null $arguments
     *
     * @return array<string, mixed>
     */
    public function functionCall(array $name, ?array $arguments, int $line): array
    {
        $node = ['type' => Opcode::FCALL->value, 'name' => $name];
        if ($arguments !== null) {
            $node['arguments'] = $arguments;
        }

        return $this->locate($node, $line);
    }

    /**
     * @param array<string, mixed>            $expr
     * @param list<array<string, mixed>>|null $trueStatements
     * @param list<array<string, mixed>>|null $falseStatements
     *
     * @return array<string, mixed>
     */
    public function ifStatement(array $expr, ?array $trueStatements, ?array $falseStatements, int $line): array
    {
        $node = ['type' => Opcode::IF->value, 'expr' => $expr];
        if ($trueStatements !== null) {
            $node['true_statements'] = $trueStatements;
        }
        if ($falseStatements !== null) {
            $node['false_statements'] = $falseStatements;
        }

        return $this->locate($node, $line);
    }

    /**
     * @param array<string, mixed>      $path
     * @param array<string, mixed>|null $params
     *
     * @return array<string, mixed>
     */
    public function includeStatement(array $path, ?array $params, int $line): array
    {
        $node = ['type' => Opcode::INCLUDE->value, 'path' => $path];
        if ($params !== null) {
            $node['params'] = $params;
        }

        return $this->locate($node, $line);
    }

    /**
     * phvolt_ret_literal_zval: a literal, identifier or raw fragment. `value`
     * is omitted for null/true/false and for an empty verbatim block.
     *
     * @return array<string, mixed>
     */
    public function literal(Opcode $type, ?string $value, int $line): array
    {
        $node = ['type' => $type->value];
        if ($value !== null) {
            $node['value'] = $value;
        }

        return $this->locate($node, $line);
    }

    /**
     * phvolt_ret_macro_call_statement (`{% call %}`).
     *
     * @param array<string, mixed>            $name
     * @param list<array<string, mixed>>|null $arguments
     * @param list<array<string, mixed>>|null $caller
     *
     * @return array<string, mixed>
     */
    public function macroCallStatement(array $name, ?array $arguments, ?array $caller, int $line): array
    {
        $node = ['type' => Opcode::CALL->value, 'name' => $name];
        if ($arguments !== null) {
            $node['arguments'] = $arguments;
        }
        if ($caller !== null) {
            $node['caller'] = $caller;
        }

        return $this->locate($node, $line);
    }

    /**
     * @param array<string, mixed>|null $default
     *
     * @return array<string, mixed>
     */
    public function macroParameter(string $variable, ?array $default, int $line): array
    {
        $node = ['variable' => $variable];
        if ($default !== null) {
            $node['default'] = $default;
        }

        return $this->locate($node, $line);
    }

    /**
     * @param list<array<string, mixed>>|null $parameters
     * @param list<array<string, mixed>>      $blockStatements
     *
     * @return array<string, mixed>
     */
    public function macroStatement(string $name, ?array $parameters, array $blockStatements, int $line): array
    {
        $node = ['type' => Opcode::MACRO->value, 'name' => $name];
        if ($parameters !== null) {
            $node['parameters'] = $parameters;
        }
        $node['block_statements'] = $blockStatements;

        return $this->locate($node, $line);
    }

    /**
     * phvolt_ret_named_item: an array item or call argument. It has no `type`.
     *
     * @param array<string, mixed> $expr
     *
     * @return array<string, mixed>
     */
    public function namedItem(?string $name, array $expr, int $line): array
    {
        $node = ['expr' => $expr];
        if ($name !== null) {
            $node['name'] = $name;
        }

        return $this->locate($node, $line);
    }

    /**
     * phvolt_ret_set_assignment: no `type`; `op` is the assignment opcode.
     *
     * @param array<string, mixed> $variable
     * @param array<string, mixed> $expr
     *
     * @return array<string, mixed>
     */
    public function setAssignment(array $variable, Opcode $operator, array $expr, int $line): array
    {
        return $this->locate(['variable' => $variable, 'op' => $operator->value, 'expr' => $expr], $line);
    }

    /**
     * phvolt_ret_set_statement: the only node without `file` and `line`.
     *
     * @param list<array<string, mixed>> $assignments
     *
     * @return array<string, mixed>
     */
    public function setStatement(array $assignments): array
    {
        return ['type' => Opcode::SET->value, 'assignments' => $assignments];
    }

    /**
     * @param array<string, mixed>      $left
     * @param array<string, mixed>|null $start
     * @param array<string, mixed>|null $end
     *
     * @return array<string, mixed>
     */
    public function slice(array $left, ?array $start, ?array $end, int $line): array
    {
        $node = ['type' => Opcode::SLICE->value, 'left' => $left];
        if ($start !== null) {
            $node['start'] = $start;
        }
        if ($end !== null) {
            $node['end'] = $end;
        }

        return $this->locate($node, $line);
    }

    /**
     * @param array<string, mixed>            $expr
     * @param list<array<string, mixed>>|null $caseClauses
     *
     * @return array<string, mixed>
     */
    public function switchStatement(array $expr, ?array $caseClauses, int $line): array
    {
        $node = ['type' => Opcode::SWITCH->value, 'expr' => $expr];
        if ($caseClauses !== null) {
            $node['case_clauses'] = $caseClauses;
        }

        return $this->locate($node, $line);
    }

    /**
     * @param array<string, mixed> $node
     *
     * @return array<string, mixed>
     */
    private function locate(array $node, int $line): array
    {
        $node['file'] = $this->file;
        $node['line'] = $line;

        return $node;
    }
}
