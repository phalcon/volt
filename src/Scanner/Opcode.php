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

namespace Phalcon\Volt\Scanner;

enum Opcode: int
{
    case ADD_ASSIGN = 46;
    case AND = 6;
    case ASSIGN = 45;
    case AUTOESCAPE = 75;
    case BLOCK = 64;
    case BREAK = 77;
    case CACHE = 66;
    case CALL = 60;
    case CASE = 42;
    case CBRACKET_CLOSE = 88;
    case CBRACKET_OPEN = 87;
    case CLOSE_DELIMITER = 32;
    case CLOSE_EDELIMITER = 63;
    case COLON = 4;
    case COMMA = 2;
    case CONCAT = 23;
    case CONTINUE = 78;
    case DECR = 28;
    case DEFAULT = 43;
    case DEFINED = 80;
    case DIVIDE = 18;
    case DIV_ASSIGN = 49;
    case DO = 73;
    case DOT = 30;
    case DOUBLE = 56;
    case ELSE = 34;
    case ELSEFOR = 36;
    case ELSEIF = 35;
    case EMPTY = 81;
    case ENDAUTOESCAPE = 76;
    case ENDBLOCK = 65;
    case ENDCACHE = 67;
    case ENDCALL = 61;
    case ENDFOR = 39;
    case ENDIF = 33;
    case ENDMACRO = 53;
    case ENDRAW = 69;
    case ENDSWITCH = 41;
    case EQUALS = 10;
    case EVEN = 82;
    case EXTENDS = 70;
    case FALSE = 58;
    case FOR = 37;
    case GREATER = 13;
    case GREATEREQUAL = 14;
    case IDENTICAL = 16;
    case IDENTIFIER = 38;
    case IF = 31;
    case IN = 8;
    case INCLUDE = 71;
    case INCR = 27;
    case INTEGER = 54;
    case IS = 9;
    case ITERABLE = 86;
    case LESS = 12;
    case LESSEQUAL = 15;
    case MACRO = 51;
    case MINUS = 22;
    case MOD = 20;
    case MUL_ASSIGN = 48;
    case NOT = 26;
    case NOTEQUALS = 11;
    case NOTIDENTICAL = 17;
    case NULL = 57;
    case NUMERIC = 84;
    case ODD = 83;
    case OPEN_DELIMITER = 1;
    case OPEN_EDELIMITER = 62;
    case OR = 7;
    case PARENTHESES_CLOSE = 52;
    case PARENTHESES_OPEN = 29;
    case PIPE = 25;
    case PLUS = 21;
    case QUESTION = 3;
    case RANGE = 5;
    case RAW = 68;
    case RAW_FRAGMENT = 79;
    case RETURN = 74;
    case SBRACKET_CLOSE = 50;
    case SBRACKET_OPEN = 24;
    case SCALAR = 85;
    case SET = 44;
    case STRING = 55;
    case SUB_ASSIGN = 47;
    case SWITCH = 40;
    case TIMES = 19;
    case TRUE = 59;
    case WITH = 72;

    public function label(): string
    {
        return match ($this) {
            self::ADD_ASSIGN => '+=',
            self::CBRACKET_CLOSE => '}',
            self::CBRACKET_OPEN => '{',
            self::CLOSE_DELIMITER => '%}',
            self::CLOSE_EDELIMITER => '}}',
            self::COLON => ':',
            self::COMMA => ',',
            self::CONCAT => '~',
            self::DECR => '--',
            self::DIVIDE => '/',
            self::DIV_ASSIGN => '/=',
            self::DOT => '.',
            self::EQUALS => '=',
            self::GREATER => '>',
            self::GREATEREQUAL => '>=',
            self::IDENTICAL => '===',
            self::INCR => '++',
            self::LESS => '<',
            self::LESSEQUAL => '<=',
            self::MINUS => '-',
            self::MOD => '%',
            self::MUL_ASSIGN => '*=',
            self::NOT => '!',
            self::NOTEQUALS => '!=',
            self::NOTIDENTICAL => '!==',
            self::OPEN_DELIMITER => '{%',
            self::OPEN_EDELIMITER => '{{',
            self::PARENTHESES_CLOSE => ')',
            self::PARENTHESES_OPEN => '(',
            self::PIPE => '|',
            self::PLUS => '+',
            self::QUESTION => '?',
            self::SBRACKET_CLOSE => ']',
            self::SBRACKET_OPEN => '[',
            self::SUB_ASSIGN => '-=',
            self::TIMES => '*',
            default => $this->name,
        };
    }
}
