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

namespace Phalcon\Volt;

use Phalcon\Volt\Compiler\Opcode;

/**
 * Token names as they appear in "Syntax error, unexpected token ..." messages.
 *
 * A port of cphalcon's `phvolt_tokens` table (ext/phalcon/mvc/view/engine/
 * volt/base.c), looked up first match wins, which is why NOT prints as `!`
 * and MOD as `%%`. Tokens missing from that table - `|`, `null`, `true`,
 * `false`, raw text, `verbatim`, `endverbatim`, `endcall` - print as UNKNOWN,
 * exactly as they do in cphalcon.
 */
final class Tokens
{
    public static function name(int $opcode): string
    {
        return match ($opcode) {
            Opcode::INTEGER->value           => 'INTEGER',
            Opcode::DOUBLE->value            => 'DOUBLE',
            Opcode::STRING->value            => 'STRING',
            Opcode::IDENTIFIER->value        => 'IDENTIFIER',
            Opcode::MINUS->value             => 'MINUS',
            Opcode::ADD->value               => '+',
            Opcode::SUB->value               => '-',
            Opcode::MUL->value               => '*',
            Opcode::DIV->value               => '/',
            Opcode::MOD->value               => '%%',
            Opcode::NOT->value               => '!',
            Opcode::CONCAT->value            => '~',
            Opcode::AND->value               => 'AND',
            Opcode::OR->value                => 'OR',
            Opcode::DOT->value               => 'DOT',
            Opcode::COMMA->value             => 'COMMA',
            Opcode::EQUALS->value            => 'EQUALS',
            Opcode::NOTEQUALS->value         => 'NOT EQUALS',
            Opcode::IDENTICAL->value         => 'IDENTICAL',
            Opcode::NOTIDENTICAL->value      => 'NOT IDENTICAL',
            Opcode::RANGE->value             => 'RANGE',
            Opcode::COLON->value             => 'COLON',
            Opcode::QUESTION->value          => 'QUESTION MARK',
            Opcode::LESS->value              => '<',
            Opcode::LESSEQUAL->value         => '<=',
            Opcode::GREATER->value           => '>',
            Opcode::GREATEREQUAL->value      => '>=',
            Opcode::PARENTHESES_OPEN->value  => '(',
            Opcode::PARENTHESES_CLOSE->value => ')',
            Opcode::SBRACKET_OPEN->value     => '[',
            Opcode::SBRACKET_CLOSE->value    => ']',
            Opcode::CBRACKET_OPEN->value     => '{',
            Opcode::CBRACKET_CLOSE->value    => '}',
            Opcode::OPEN_DELIMITER->value    => '{%',
            Opcode::CLOSE_DELIMITER->value   => '%}',
            Opcode::OPEN_EDELIMITER->value   => '{{',
            Opcode::CLOSE_EDELIMITER->value  => '}}',
            Opcode::IF->value                => 'IF',
            Opcode::ELSE->value              => 'ELSE',
            Opcode::ELSEIF->value            => 'ELSEIF',
            Opcode::ELSEFOR->value           => 'ELSEFOR',
            Opcode::ENDIF->value             => 'ENDIF',
            Opcode::FOR->value               => 'FOR',
            Opcode::SWITCH->value            => 'SWITCH',
            Opcode::CASE->value              => 'CASE',
            Opcode::DEFAULT->value           => 'DEFAULT',
            Opcode::ENDSWITCH->value         => 'ENDSWITCH',
            Opcode::IN->value                => 'IN',
            Opcode::ENDFOR->value            => 'ENDFOR',
            Opcode::SET->value               => 'SET',
            Opcode::ASSIGN->value            => 'ASSIGN',
            Opcode::ADD_ASSIGN->value        => '+=',
            Opcode::SUB_ASSIGN->value        => '-=',
            Opcode::MUL_ASSIGN->value        => '*=',
            Opcode::DIV_ASSIGN->value        => '/=',
            Opcode::INCR->value              => '++',
            Opcode::DECR->value              => '--',
            Opcode::BLOCK->value             => 'BLOCK',
            Opcode::ENDBLOCK->value          => 'ENDBLOCK',
            Opcode::EXTENDS->value           => 'EXTENDS',
            Opcode::IS->value                => 'IS',
            Opcode::DEFINED->value           => 'DEFINED',
            Opcode::EMPTY->value             => 'EMPTY',
            Opcode::EVEN->value              => 'EVEN',
            Opcode::ODD->value               => 'ODD',
            Opcode::NUMERIC->value           => 'NUMERIC',
            Opcode::SCALAR->value            => 'SCALAR',
            Opcode::ITERABLE->value          => 'ITERABLE',
            Opcode::INCLUDE->value           => 'INCLUDE',
            Opcode::DO->value                => 'DO',
            Opcode::IGNORE->value            => 'WHITESPACE',
            Opcode::AUTOESCAPE->value        => 'AUTOESCAPE',
            Opcode::ENDAUTOESCAPE->value     => 'ENDAUTOESCAPE',
            Opcode::CONTINUE->value          => 'CONTINUE',
            Opcode::BREAK->value             => 'BREAK',
            Opcode::WITH->value              => 'WITH',
            Opcode::RETURN->value            => 'RETURN',
            Opcode::MACRO->value             => 'MACRO',
            Opcode::ENDMACRO->value          => 'ENDMACRO',
            Opcode::CALL->value              => 'CALL',
            default                          => 'UNKNOWN',
        };
    }
}
