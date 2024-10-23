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

/**
 * Next is all token values, in a form suitable for use by makeheaders.
 * This section will be null unless lemon is run with the -m switch.
 * These constants (all generated automatically by the parser generator)
 * specify the various kinds of tokens (terminals) that the parser
 * understands.
 *
 * Each symbol here is a terminal symbol in the grammar.
 */
class Opcode
{
    public const PHVOLT_ADD_ASSIGN        = 46;
    public const PHVOLT_AND               = 6;
    public const PHVOLT_ASSIGN            = 45;
    public const PHVOLT_AUTOESCAPE        = 75;
    public const PHVOLT_BLOCK             = 64;
    public const PHVOLT_BREAK             = 77;
    public const PHVOLT_CACHE             = 66;
    public const PHVOLT_CALL              = 60;
    public const PHVOLT_CASE              = 42;
    public const PHVOLT_CBRACKET_CLOSE    = 88;
    public const PHVOLT_CBRACKET_OPEN     = 87;
    public const PHVOLT_CLOSE_DELIMITER   = 32;
    public const PHVOLT_CLOSE_EDELIMITER  = 63;
    public const PHVOLT_COLON             = 4;
    public const PHVOLT_COMMA             = 2;
    public const PHVOLT_CONCAT            = 23;
    public const PHVOLT_CONTINUE          = 78;
    public const PHVOLT_DECR              = 28;
    public const PHVOLT_DEFAULT           = 43;
    public const PHVOLT_DEFINED           = 80;
    public const PHVOLT_DIVIDE            = 18;
    public const PHVOLT_DIV_ASSIGN        = 49;
    public const PHVOLT_DO                = 73;
    public const PHVOLT_DOT               = 30;
    public const PHVOLT_DOUBLE            = 56;
    public const PHVOLT_ELSE              = 34;
    public const PHVOLT_ELSEFOR           = 36;
    public const PHVOLT_ELSEIF            = 35;
    public const PHVOLT_EMPTY             = 81;
    public const PHVOLT_ENDAUTOESCAPE     = 76;
    public const PHVOLT_ENDBLOCK          = 65;
    public const PHVOLT_ENDCACHE          = 67;
    public const PHVOLT_ENDCALL           = 61;
    public const PHVOLT_ENDFOR            = 39;
    public const PHVOLT_ENDIF             = 33;
    public const PHVOLT_ENDMACRO          = 53;
    public const PHVOLT_ENDRAW            = 69;
    public const PHVOLT_ENDSWITCH         = 41;
    public const PHVOLT_EQUALS            = 10;
    public const PHVOLT_EVEN              = 82;
    public const PHVOLT_EXTENDS           = 70;
    public const PHVOLT_FALSE             = 58;
    public const PHVOLT_FOR               = 37;
    public const PHVOLT_GREATER           = 13;
    public const PHVOLT_GREATEREQUAL      = 14;
    public const PHVOLT_IDENTICAL         = 16;
    public const PHVOLT_IDENTIFIER        = 38;
    public const PHVOLT_IF                = 31;
    public const PHVOLT_IN                = 8;
    public const PHVOLT_INCLUDE           = 71;
    public const PHVOLT_INCR              = 27;
    public const PHVOLT_INTEGER           = 54;
    public const PHVOLT_IS                = 9;
    public const PHVOLT_ITERABLE          = 86;
    public const PHVOLT_LESS              = 12;
    public const PHVOLT_LESSEQUAL         = 15;
    public const PHVOLT_MACRO             = 51;
    public const PHVOLT_MINUS             = 22;
    public const PHVOLT_MOD               = 20;
    public const PHVOLT_MUL_ASSIGN        = 48;
    public const PHVOLT_NOT               = 26;
    public const PHVOLT_NOTEQUALS         = 11;
    public const PHVOLT_NOTIDENTICAL      = 17;
    public const PHVOLT_NULL              = 57;
    public const PHVOLT_NUMERIC           = 84;
    public const PHVOLT_ODD               = 83;
    public const PHVOLT_OPEN_DELIMITER    = 1;
    public const PHVOLT_OPEN_EDELIMITER   = 62;
    public const PHVOLT_OR                = 7;
    public const PHVOLT_PARENTHESES_CLOSE = 52;
    public const PHVOLT_PARENTHESES_OPEN  = 29;
    public const PHVOLT_PIPE              = 25;
    public const PHVOLT_PLUS              = 21;
    public const PHVOLT_QUESTION          = 3;
    public const PHVOLT_RANGE             = 5;
    public const PHVOLT_RAW               = 68;
    public const PHVOLT_RAW_FRAGMENT      = 79;
    public const PHVOLT_RETURN            = 74;
    public const PHVOLT_SBRACKET_CLOSE    = 50;
    public const PHVOLT_SBRACKET_OPEN     = 24;
    public const PHVOLT_SCALAR            = 85;
    public const PHVOLT_SET               = 44;
    public const PHVOLT_STRING            = 55;
    public const PHVOLT_SUB_ASSIGN        = 47;
    public const PHVOLT_SWITCH            = 40;
    public const PHVOLT_TIMES             = 19;
    public const PHVOLT_TRUE              = 59;
    public const PHVOLT_WITH              = 72;
}
