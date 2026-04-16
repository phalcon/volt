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

namespace Phalcon\Volt\Compiler;

enum Opcode: int
{
    case ADD             = 43;
    case ADD_ASSIGN      = 281;
    case AND             = 266;
    case ARRAY           = 360;
    case ARRAYACCESS     = 361;
    case ASSIGN          = 61;
    case AUTOESCAPE      = 317;
    case BLOCK           = 307;
    case BREAK           = 320;
    case CACHE           = 314;
    case CALL            = 325;
    case CASE            = 412;
    case CBRACKET_CLOSE  = 125;
    case CBRACKET_OPEN   = 123;
    case CLOSE_DELIMITER    = 331;
    case CLOSE_EDELIMITER   = 333;
    case COLON           = 277;
    case COMMA           = 269;
    case CONCAT          = 126;
    case CONTINUE        = 319;
    case DECR            = 280;
    case DEFAULT         = 413;
    case DEFINED         = 312;
    case DIV             = 47;
    case DIV_ASSIGN      = 284;
    case DO              = 316;
    case DOT             = 46;
    case DOUBLE          = 259;
    case ECHO            = 359;
    case ELSE            = 301;
    case ELSEFOR         = 321;
    case ELSEIF          = 302;
    case EMPTY           = 380;
    case EMPTY_STATEMENT = 358;
    case ENCLOSED        = 356;
    case ENDAUTOESCAPE   = 318;
    case ENDBLOCK        = 308;
    case ENDCACHE        = 315;
    case ENDCALL         = 326;
    case ENDFOR          = 305;
    case ENDIF           = 303;
    case ENDMACRO        = 323;
    case ENDRAW          = 401;
    case ENDSWITCH       = 414;
    case EQUALS          = 272;
    case EVEN            = 381;
    case EXPR            = 354;
    case EXTENDS         = 310;
    case FALSE           = 262;
    case FCALL           = 350;
    case FOR             = 304;
    case GREATER         = 62;
    case GREATEREQUAL    = 271;
    case IDENTICAL       = 274;
    case IDENTIFIER      = 265;
    case IF              = 300;
    case IGNORE          = 257;
    case IN              = 309;
    case INCLUDE         = 313;
    case INCR            = 279;
    case INTEGER         = 258;
    case IS              = 311;
    case ISEMPTY         = 386;
    case ISEVEN          = 387;
    case ISITERABLE      = 391;
    case ISNUMERIC       = 389;
    case ISODD           = 388;
    case ISSCALAR        = 390;
    case ISSET           = 363;
    case ITERABLE        = 385;
    case LESS            = 60;
    case LESSEQUAL       = 270;
    case MACRO           = 322;
    case MINUS           = 368;
    case MOD             = 37;
    case MUL             = 42;
    case MUL_ASSIGN      = 283;
    case NOT             = 33;
    case NOTEQUALS       = 273;
    case NOTIDENTICAL    = 275;
    case NOT_IN          = 367;
    case NOT_ISEMPTY     = 392;
    case NOT_ISEVEN      = 393;
    case NOT_ISITERABLE  = 397;
    case NOT_ISNUMERIC   = 395;
    case NOT_ISODD       = 394;
    case NOT_ISSCALAR    = 396;
    case NOT_ISSET       = 362;
    case NULL            = 261;
    case NUMERIC         = 383;
    case ODD             = 382;
    case OPEN_DELIMITER     = 330;
    case OPEN_EDELIMITER    = 332;
    case OR              = 267;
    case PARENTHESES_CLOSE = 41;
    case PARENTHESES_OPEN  = 40;
    case PIPE            = 124;
    case PLUS            = 369;
    case POW             = 278;
    case QUALIFIED       = 355;
    case QUESTION        = 63;
    case RANGE           = 276;
    case RAW             = 400;
    case RAW_FRAGMENT    = 357;
    case RESOLVED_EXPR   = 364;
    case RETURN          = 327;
    case SBRACKET_CLOSE  = 93;
    case SBRACKET_OPEN   = 91;
    case SCALAR          = 384;
    case SET             = 306;
    case SLICE           = 365;
    case STRING          = 260;
    case SUB             = 45;
    case SUB_ASSIGN      = 282;
    case SWITCH          = 411;
    case TERNARY         = 366;
    case TRUE            = 263;
    case WITH            = 324;
}
