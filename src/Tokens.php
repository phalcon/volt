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

class Tokens
{
    public static array $names = [
        'INTEGER' => Compiler::PHVOLT_T_INTEGER,
        'DOUBLE' => Compiler::PHVOLT_T_DOUBLE,
        'STRING' => Compiler::PHVOLT_T_STRING,
        'IDENTIFIER' => Compiler::PHVOLT_T_IDENTIFIER,
        'MINUS' => Compiler::PHVOLT_T_MINUS,
        '+' => Compiler::PHVOLT_T_ADD,
        '-' => Compiler::PHVOLT_T_SUB,
        '*' => Compiler::PHVOLT_T_MUL,
        '/' => Compiler::PHVOLT_T_DIV,
        '%%' => Compiler::PHVOLT_T_MOD,
        '!' => Compiler::PHVOLT_T_NOT,
        '~' => Compiler::PHVOLT_T_CONCAT,
        'AND' => Compiler::PHVOLT_T_AND,
        'OR' => Compiler::PHVOLT_T_OR,
        'DOT' => Compiler::PHVOLT_T_DOT,
        'COMMA' => Compiler::PHVOLT_T_COMMA,
        'EQUALS' => Compiler::PHVOLT_T_EQUALS,
        'NOT EQUALS' => Compiler::PHVOLT_T_NOTEQUALS,
        'IDENTICAL' => Compiler::PHVOLT_T_IDENTICAL,
        'NOT IDENTICAL' => Compiler::PHVOLT_T_NOTIDENTICAL,
        'NOT' => Compiler::PHVOLT_T_NOT,
        'RANGE' => Compiler::PHVOLT_T_RANGE,
        'COLON' => Compiler::PHVOLT_T_COLON,
        'QUESTION MARK' => Compiler::PHVOLT_T_QUESTION,
        '<' => Compiler::PHVOLT_T_LESS,
        '<=' => Compiler::PHVOLT_T_LESSEQUAL,
        '>' => Compiler::PHVOLT_T_GREATER,
        '>=' => Compiler::PHVOLT_T_GREATEREQUAL,
        '(' => Compiler::PHVOLT_T_PARENTHESES_OPEN,
        ')' => Compiler::PHVOLT_T_PARENTHESES_CLOSE,
        '[' => Compiler::PHVOLT_T_SBRACKET_OPEN,
        ']' => Compiler::PHVOLT_T_SBRACKET_CLOSE,
        '{' => Compiler::PHVOLT_T_CBRACKET_OPEN,
        '}' => Compiler::PHVOLT_T_CBRACKET_CLOSE,
        '{%' => Compiler::PHVOLT_T_OPEN_DELIMITER,
        '%}' => Compiler::PHVOLT_T_CLOSE_DELIMITER,
        '{{' => Compiler::PHVOLT_T_OPEN_EDELIMITER,
        '}}' => Compiler::PHVOLT_T_CLOSE_EDELIMITER,
        'IF' => Compiler::PHVOLT_T_IF,
        'ELSE' => Compiler::PHVOLT_T_ELSE,
        'ELSEIF' => Compiler::PHVOLT_T_ELSEIF,
        'ELSEFOR' => Compiler::PHVOLT_T_ELSEFOR,
        'ENDIF' => Compiler::PHVOLT_T_ENDIF,
        'FOR' => Compiler::PHVOLT_T_FOR,
        'SWITCH' => Compiler::PHVOLT_T_SWITCH,
        'CASE' => Compiler::PHVOLT_T_CASE,
        'DEFAULT' => Compiler::PHVOLT_T_DEFAULT,
        'ENDSWITCH' => Compiler::PHVOLT_T_ENDSWITCH,
        'IN' => Compiler::PHVOLT_T_IN,
        'ENDFOR' => Compiler::PHVOLT_T_ENDFOR,
        'SET' => Compiler::PHVOLT_T_SET,
        'ASSIGN' => Compiler::PHVOLT_T_ASSIGN,
        '+=' => Compiler::PHVOLT_T_ADD_ASSIGN,
        '-=' => Compiler::PHVOLT_T_SUB_ASSIGN,
        '*=' => Compiler::PHVOLT_T_MUL_ASSIGN,
        '/=' => Compiler::PHVOLT_T_DIV_ASSIGN,
        '++' => Compiler::PHVOLT_T_INCR,
        '--' => Compiler::PHVOLT_T_DECR,
        'BLOCK' => Compiler::PHVOLT_T_BLOCK,
        'ENDBLOCK' => Compiler::PHVOLT_T_ENDBLOCK,
        'CACHE' => Compiler::PHVOLT_T_CACHE,
        'ENDCACHE' => Compiler::PHVOLT_T_ENDCACHE,
        'EXTENDS' => Compiler::PHVOLT_T_EXTENDS,
        'IS' => Compiler::PHVOLT_T_IS,
        'DEFINED' => Compiler::PHVOLT_T_DEFINED,
        'EMPTY' => Compiler::PHVOLT_T_EMPTY,
        'EVEN' => Compiler::PHVOLT_T_EVEN,
        'ODD' => Compiler::PHVOLT_T_ODD,
        'NUMERIC' => Compiler::PHVOLT_T_NUMERIC,
        'SCALAR' => Compiler::PHVOLT_T_SCALAR,
        'ITERABLE' => Compiler::PHVOLT_T_ITERABLE,
        'INCLUDE' => Compiler::PHVOLT_T_INCLUDE,
        'DO' => Compiler::PHVOLT_T_DO,
        'WHITESPACE' => Compiler::PHVOLT_T_IGNORE,
        'AUTOESCAPE' => Compiler::PHVOLT_T_AUTOESCAPE,
        'ENDAUTOESCAPE' => Compiler::PHVOLT_T_ENDAUTOESCAPE,
        'CONTINUE' => Compiler::PHVOLT_T_CONTINUE,
        'BREAK' => Compiler::PHVOLT_T_BREAK,
        'WITH' => Compiler::PHVOLT_T_WITH,
        'RETURN' => Compiler::PHVOLT_T_RETURN,
        'MACRO' => Compiler::PHVOLT_T_MACRO,
        'ENDMACRO' => Compiler::PHVOLT_T_ENDMACRO,
        'CALL' => Compiler::PHVOLT_T_CALL,
        null => 0,
    ];
}
