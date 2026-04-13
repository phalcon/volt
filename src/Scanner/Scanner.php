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

use Phalcon\Volt\Compiler;
use Phalcon\Volt\Scanner\ScannerStatus;

class Scanner
{
    private Token $token;

    public function __construct(private State $state)
    {
        $this->token = new Token();
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function scanForToken(): ScannerStatus
    {
        $start = $this->state->getCursor();
        $status = ScannerStatus::IMPOSSIBLE;
        /** @phpstan-ignore identical.alwaysTrue */
        while (ScannerStatus::IMPOSSIBLE === $status) {
            $cursor = $this->state->getStart();
            $mode = $this->state->getMode();
            if ($mode === Compiler::PHVOLT_MODE_RAW || $mode === Compiler::PHVOLT_MODE_COMMENT) {
                $next       = $this->state->getNext();
                $doubleNext = $this->state->getNext(2);

                if ($cursor === "\n") {
                    $this->state->incrementActiveLine();
                }

                if ($cursor === null || ($cursor === '{' && ($next === '%' || $next === '{' || $next === '#'))) {
                    if ($next !== '#') {
                        $this->state->setMode(Compiler::PHVOLT_MODE_CODE);

                        if ($this->state->getRawFragment() !== '') {
                            if ($this->state->getWhitespaceControl()) {
                                $this->state->setRawFragment(ltrim($this->state->getRawFragment()));
                                $this->state->setWhitespaceControl(false);
                            }

                            if ($doubleNext === '-') {
                                $this->state->setRawFragment(rtrim($this->state->getRawFragment()));
                            }

                            $this->token = new Token(Compiler::PHVOLT_T_RAW_FRAGMENT, $this->state->getRawFragment());

                            $this->state->setRawFragment('');
                        } else {
                            $this->token = new Token(Compiler::PHVOLT_T_IGNORE);
                        }
                    } else {
                        while ($next = $this->state->incrementStart()->getStart()) {
                            $doubleNext = $this->state->getNext();
                            if ($next === '#' && $doubleNext === '}') {
                                $this->state->incrementStart(2);
                                $this->token = new Token(Compiler::PHVOLT_T_IGNORE);
                                return ScannerStatus::OK;
                            } elseif ($next === "\n") {
                                $this->state->incrementActiveLine();
                            }
                        }

                        return ScannerStatus::EOF;
                    }

                    return ScannerStatus::OK;
                }

                $this->state->appendToRawFragment($cursor);
                $this->state->incrementStart();
            } else {
                $vvch = $cursor;
                switch ($vvch) {
                    case null:
                        goto vv2;
                    case "\t":
                    case "\r":
                    case ' ':
                        goto vv6;
                    case "\n":
                        goto vv9;
                    case '!':
                        goto vv11;
                    case '"':
                        goto vv13;
                    case '%':
                        goto vv14;
                    case '\'':
                        goto vv16;
                    case '(':
                        goto vv17;
                    case ')':
                        goto vv19;
                    case '*':
                        goto vv21;
                    case '+':
                        goto vv23;
                    case ',':
                        goto vv25;
                    case '-':
                        goto vv27;
                    case '.':
                        goto vv29;
                    case '/':
                        goto vv31;
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                        goto vv33;
                    case ':':
                        goto vv36;
                    case '<':
                        goto vv38;
                    case '=':
                        goto vv40;
                    case '>':
                        goto vv42;
                    case '?':
                        goto vv44;
                    case 'A':
                    case 'a':
                        goto vv46;
                    case 'B':
                    case 'b':
                        goto vv48;
                    case 'C':
                    case 'c':
                        goto vv49;
                    case 'D':
                    case 'd':
                        goto vv50;
                    case 'E':
                    case 'e':
                        goto vv51;
                    case 'F':
                    case 'f':
                        goto vv52;
                    case 'G':
                    case 'H':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'P':
                    case 'Q':
                    case 'U':
                    case 'V':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '_':
                    case 'g':
                    case 'h':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'p':
                    case 'q':
                    case 'u':
                    case 'v':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    case 'I':
                        goto vv55;
                    case 'M':
                    case 'm':
                        goto vv56;
                    case 'N':
                    case 'n':
                        goto vv57;
                    case 'O':
                    case 'o':
                        goto vv58;
                    case 'R':
                    case 'r':
                        goto vv59;
                    case 'S':
                    case 's':
                        goto vv60;
                    case 'T':
                    case 't':
                        goto vv61;
                    case 'W':
                    case 'w':
                        goto vv62;
                    case '[':
                        goto vv63;
                    case '\\':
                        goto vv65;
                    case ']':
                        goto vv66;
                    case 'i':
                        goto vv68;
                    case '{':
                        goto vv69;
                    case '|':
                        goto vv71;
                    case '}':
                        goto vv73;
                    case '~':
                        goto vv75;
                    default:
                        goto vv4;
                }

                vv2:
                $status = ScannerStatus::EOF;
                break;

                vv4:
                $this->state->incrementStart();

                vv5:
                $status = ScannerStatus::ERR;
                break;
                vv6:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '\t':
                    case '\r':
                    case ' ':
                        goto vv6;
                    default:
                        goto vv8;
                }
                vv8:
                $this->token = new Token(Compiler::PHVOLT_T_IGNORE);
                return ScannerStatus::OK;
                vv9:
                $this->state->incrementStart();
                $this->state->incrementActiveLine();
                $this->token = new Token(Compiler::PHVOLT_T_IGNORE);
                return ScannerStatus::OK;
                vv11:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv77;
                    default:
                        goto vv12;
                }
                vv12:
                $this->token = new Token(Compiler::PHVOLT_T_NOT);
                return ScannerStatus::OK;

                vv13:
                $vvaccept = 0;
                $vvch     = $this->state->incrementStart()->getStart();
                $this->state->setMarker($this->state->getCursor());
                if ($vvch === null) {
                    goto vv5;
                }
                goto vv80;

                vv14:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '}':
                        goto vv85;
                    default:
                        goto vv15;
                }
                vv15:
                $this->token = new Token(Compiler::PHVOLT_T_MOD);
                return ScannerStatus::OK;

                vv16:
                $vvaccept = 0;
                $vvch     = $this->state->incrementStart()->getStart();
                $this->state->setMarker($this->state->getCursor());
                if ($vvch === null) {
                    goto vv5;
                }
                goto vv88;

                vv17:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_PARENTHESES_OPEN);
                return ScannerStatus::OK;
                vv19:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_PARENTHESES_CLOSE);
                    return ScannerStatus::OK;
                }
                vv21:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv90;
                    default:
                        goto vv22;
                }
                vv22:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_MUL);
                    return ScannerStatus::OK;
                }
                vv23:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '+':
                        goto vv92;
                    case '=':
                        goto vv94;
                    default:
                        goto vv24;
                }

                vv24:
                $this->token = new Token(Compiler::PHVOLT_T_ADD);
                return ScannerStatus::OK;

                vv25:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_COMMA);
                return ScannerStatus::OK;

                vv27:
                $vvaccept = 1;
                $vvch     = $this->state->incrementStart()->getStart();
                $this->state->setMarker($this->state->getCursor());
                switch ($vvch) {
                    case '%':
                        goto vv96;
                    case '-':
                        goto vv97;
                    case '=':
                        goto vv99;
                    case '}':
                        goto vv101;
                    default:
                        goto vv28;
                }

                vv28:
                $this->token = new Token(Compiler::PHVOLT_T_SUB);
                return ScannerStatus::OK;

                vv29:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '.':
                        goto vv102;
                    default:
                        goto vv30;
                }

                vv30:
                $this->token = new Token(Compiler::PHVOLT_T_DOT);
                return ScannerStatus::OK;

                vv31:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv104;
                    default:
                        goto vv32;
                }

                vv32:
                $this->token = new Token(Compiler::PHVOLT_T_DIV);
                return ScannerStatus::OK;

                vv33:
                $vvaccept = 2;
                $vvch     = $this->state->incrementStart()->getStart();
                $this->state->setMarker($this->state->getCursor());
                switch ($vvch) {
                    case '.':
                        goto vv106;
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                        goto vv33;
                    default:
                        goto vv35;
                }

                vv35:
                $this->token = new Token(
                    Compiler::PHVOLT_T_INTEGER,
                    substr($this->state->getRawBuffer(), $start, $this->state->getCursor() - $start)
                );
                return ScannerStatus::OK;

                vv36:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_COLON);
                return ScannerStatus::OK;
                vv38:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv107;
                    case '>':
                        goto vv109;
                    default:
                        goto vv39;
                }
                vv39:
                $this->token = new Token(Compiler::PHVOLT_T_LESS);
                return ScannerStatus::OK;
                vv40:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv111;
                    default:
                        goto vv41;
                }
                vv41:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ASSIGN);
                    return ScannerStatus::OK;
                }
                vv42:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv113;
                    default:
                        goto vv43;
                }
                vv43:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_GREATER);
                    return ScannerStatus::OK;
                }
                vv44:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_QUESTION);
                    return ScannerStatus::OK;
                }
                vv46:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv115;
                    case 'U':
                    case 'u':
                        goto vv116;
                    default:
                        goto vv54;
                }

                vv47:
                $this->token = new Token(
                    Compiler::PHVOLT_T_IDENTIFIER,
                    substr($this->state->getRawBuffer(), $start, $this->state->getCursor() - $start)
                );
                return ScannerStatus::OK;

                vv48:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv117;
                    case 'R':
                    case 'r':
                        goto vv118;
                    default:
                        goto vv54;
                }
                vv49:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv119;
                    case 'O':
                    case 'o':
                        goto vv120;
                    default:
                        goto vv54;
                }
                vv50:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv121;
                    case 'O':
                    case 'o':
                        goto vv122;
                    default:
                        goto vv54;
                }
                vv51:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv124;
                    case 'M':
                    case 'm':
                        goto vv125;
                    case 'N':
                    case 'n':
                        goto vv126;
                    case 'V':
                    case 'v':
                        goto vv127;
                    case 'X':
                    case 'x':
                        goto vv128;
                    default:
                        goto vv54;
                }
                vv52:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv129;
                    case 'O':
                    case 'o':
                        goto vv130;
                    default:
                        goto vv54;
                }

                vv53:
                $vvch = $this->state->incrementStart()->getStart();

                vv54:
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv47;
                }
                vv55:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'F':
                    case 'f':
                        goto vv131;
                    case 'N':
                    case 'n':
                        goto vv133;
                    case 'S':
                    case 's':
                        goto vv135;
                    case 'T':
                    case 't':
                        goto vv137;
                    default:
                        goto vv54;
                }
                vv56:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv138;
                    default:
                        goto vv54;
                }
                vv57:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv139;
                    case 'U':
                    case 'u':
                        goto vv140;
                    default:
                        goto vv54;
                }
                vv58:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'D':
                    case 'd':
                        goto vv141;
                    case 'R':
                    case 'r':
                        goto vv142;
                    default:
                        goto vv54;
                }
                vv59:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv144;
                    case 'E':
                    case 'e':
                        goto vv145;
                    default:
                        goto vv54;
                }
                vv60:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv146;
                    case 'E':
                    case 'e':
                        goto vv147;
                    case 'W':
                    case 'w':
                        goto vv148;
                    default:
                        goto vv54;
                }
                vv61:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv149;
                    default:
                        goto vv54;
                }
                vv62:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'I':
                    case 'i':
                        goto vv150;
                    default:
                        goto vv54;
                }
                vv63:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_SBRACKET_OPEN);
                    return ScannerStatus::OK;
                }
                vv65:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv5;
                }
                vv66:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_SBRACKET_CLOSE);
                    return ScannerStatus::OK;
                }
                vv68:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'F':
                    case 'f':
                        goto vv131;
                    case 'N':
                    case 'n':
                        goto vv133;
                    case 'S':
                        goto vv135;
                    case 'T':
                    case 't':
                        goto vv137;
                    case 's':
                        goto vv151;
                    default:
                        goto vv54;
                }
                vv69:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '%':
                        goto vv152;
                    case '{':
                        goto vv154;
                    default:
                        goto vv70;
                }
                vv70:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_CBRACKET_OPEN);
                    return ScannerStatus::OK;
                }
                vv71:
                    $this->state->incrementStart();
                    $this->token = new Token(Compiler::PHVOLT_T_PIPE);
                    return ScannerStatus::OK;
                vv73:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '}':
                        goto vv156;
                    default:
                        goto vv74;
                }
                vv74:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_CBRACKET_CLOSE);
                    return ScannerStatus::OK;
                }
                vv75:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_CONCAT);
                    return ScannerStatus::OK;
                }
                vv77:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv158;
                    default:
                        goto vv78;
                }
                vv78:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_NOTEQUALS);
                    return ScannerStatus::OK;
                }
                vv79:
                $vvch = $this->state->incrementStart()->getStart();
                vv80:
                switch ($vvch) {
                    case null:
                        goto vv81;
                    case '"':
                        goto vv82;
                    case '\\':
                        goto vv84;
                    default:
                        goto vv79;
                }

                vv81:
                $this->state->setCursor($this->state->getMarker());
                switch ($vvaccept) {
                    case 0:
                        goto vv5;
                    case 1:
                        goto vv28;
                    case 2:
                        goto vv35;
                    default:
                        goto vv136;
                }

                vv82:
                $this->state->incrementStart();
                $start++;
                $this->token = new Token(
                    Compiler::PHVOLT_T_STRING,
                    substr($this->state->getRawBuffer(), $start, $this->state->getCursor() - $start - 1)
                );
                return ScannerStatus::OK;
                vv84:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case "\n":
                        goto vv81;
                    default:
                        goto vv79;
                }
                vv85:
                $this->state->incrementStart();
                $this->state->setMode(Compiler::PHVOLT_MODE_RAW);
                $this->token = new Token(Compiler::PHVOLT_T_CLOSE_DELIMITER);
                return ScannerStatus::OK;
                vv87:
                $vvch = $this->state->incrementStart()->getStart();
                vv88:
                switch ($vvch) {
                    case 0x00:
                        goto vv81;
                    case '\'':
                        goto vv82;
                    case '\\':
                        goto vv89;
                    default:
                        goto vv87;
                }
                vv89:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case "\n":
                        goto vv81;
                    default:
                        goto vv87;
                }
                vv90:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_MUL_ASSIGN);
                    return ScannerStatus::OK;
                }
                vv92:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_INCR);
                    return ScannerStatus::OK;
                }
                vv94:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ADD_ASSIGN);
                    return ScannerStatus::OK;
                }
                vv96:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '}':
                        goto vv160;
                    default:
                        goto vv81;
                }

                vv97:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_DECR);
                return ScannerStatus::OK;

                vv99:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_SUB_ASSIGN);
                return ScannerStatus::OK;

                vv101:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '}':
                        goto vv162;
                    default:
                        goto vv81;
                }

                vv102:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_RANGE);
                return ScannerStatus::OK;

                vv104:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_DIV_ASSIGN);
                return ScannerStatus::OK;

                vv106:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                        goto vv164;
                    default:
                        goto vv81;
                }
                vv107:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_LESSEQUAL);
                    return ScannerStatus::OK;
                }
                vv109:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_NOTEQUALS);
                    return ScannerStatus::OK;
                }
                vv111:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '=':
                        goto vv167;
                    default:
                        goto vv112;
                }
                vv112:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_EQUALS);
                    return ScannerStatus::OK;
                }
                vv113:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_GREATEREQUAL);
                    return ScannerStatus::OK;
                }
                vv115:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'D':
                    case 'd':
                        goto vv169;
                    default:
                        goto vv54;
                }
                vv116:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv171;
                    default:
                        goto vv54;
                }
                vv117:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv172;
                    default:
                        goto vv54;
                }
                vv118:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv173;
                    default:
                        goto vv54;
                }
                vv119:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv174;
                    case 'L':
                    case 'l':
                        goto vv175;
                    case 'S':
                    case 's':
                        goto vv176;
                    default:
                        goto vv54;
                }
                vv120:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv177;
                    default:
                        goto vv54;
                }
                vv121:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'F':
                    case 'f':
                        goto vv178;
                    default:
                        goto vv54;
                }
                vv122:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv123;
                }

                vv123:
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_DO);
                return ScannerStatus::OK;

                vv124:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'S':
                    case 's':
                        goto vv179;
                    default:
                        goto vv54;
                }
                vv125:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'P':
                    case 'p':
                        goto vv180;
                    default:
                        goto vv54;
                }
                vv126:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'D':
                    case 'd':
                        goto vv181;
                    default:
                        goto vv54;
                }
                vv127:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv182;
                    default:
                        goto vv54;
                }
                vv128:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv183;
                    default:
                        goto vv54;
                }
                vv129:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv184;
                    default:
                        goto vv54;
                }
                vv130:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv185;
                    default:
                        goto vv54;
                }
                vv131:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv132;
                }
                vv132:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_IF);
                    return ScannerStatus::OK;
                }
                vv133:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    case 'C':
                    case 'c':
                        goto vv187;
                    default:
                        goto vv134;
                }

                vv134:
                $this->token = new Token(Compiler::PHVOLT_T_IN);
                return ScannerStatus::OK;

                vv135:
                $vvaccept = 3;
                $vvch     = $this->state->incrementStart()->getStart();
                $this->state->setMarker($this->state->getCursor());
                switch ($vvch) {
                    case ' ':
                        goto vv188;
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv136;
                }

                vv136:
                if ($this->state->getActiveToken() === Compiler::PHVOLT_T_DOT) {
                    $this->token = new Token(
                        Compiler::PHVOLT_T_IDENTIFIER,
                        substr($this->state->getRawBuffer(), $start, $this->state->getCursor() - $start)
                    );
                } else {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_IS);
                }

                return ScannerStatus::OK;

                vv137:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv189;
                    default:
                        goto vv54;
                }
                vv138:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv190;
                    default:
                        goto vv54;
                }
                vv139:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv191;
                    default:
                        goto vv54;
                }
                vv140:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv193;
                    case 'M':
                    case 'm':
                        goto vv194;
                    default:
                        goto vv54;
                }
                vv141:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'D':
                    case 'd':
                        goto vv195;
                    default:
                        goto vv54;
                }
                vv142:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv143;
                }
                vv143:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_OR);
                    return ScannerStatus::OK;
                }
                vv144:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'W':
                    case 'w':
                        goto vv197;
                    default:
                        goto vv54;
                }
                vv145:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv199;
                    default:
                        goto vv54;
                }
                vv146:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv200;
                    default:
                        goto vv54;
                }
                vv147:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv201;
                    default:
                        goto vv54;
                }
                vv148:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'I':
                    case 'i':
                        goto vv203;
                    default:
                        goto vv54;
                }
                vv149:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'U':
                    case 'u':
                        goto vv204;
                    default:
                        goto vv54;
                }
                vv150:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv205;
                    default:
                        goto vv54;
                }
                vv151:
                $vvaccept = 3;
                $vvch     = $this->state->incrementStart()->getStart();
                $this->state->setMarker($this->state->getCursor());
                switch ($vvch) {
                    case ' ':
                        goto vv206;
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv136;
                }
                vv152:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '-':
                        goto vv207;
                    default:
                        goto vv153;
                }

                vv153:
                $this->state->setWhitespaceControl(false);
                $this->token = new Token(Compiler::PHVOLT_T_OPEN_DELIMITER);
                return ScannerStatus::OK;

                vv154:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '-':
                        goto vv209;
                    default:
                        goto vv155;
                }

                vv155:
                $this->state->setWhitespaceControl(false);
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_OPEN_EDELIMITER);
                return ScannerStatus::OK;

                vv156:
                $this->state->incrementStart();
                $this->state->setMode(Compiler::PHVOLT_MODE_RAW);
                $this->token = new Token(Compiler::PHVOLT_T_CLOSE_EDELIMITER);
                return ScannerStatus::OK;
                vv158:
                $this->state->incrementStart();
                $this->token = new Token(Compiler::PHVOLT_T_NOTIDENTICAL);
                return ScannerStatus::OK;

                vv160:
                $this->state->incrementStart();
                $this->state->setMode(Compiler::PHVOLT_MODE_RAW);
                $this->state->setWhitespaceControl(true);
                $this->token = new Token(Compiler::PHVOLT_T_CLOSE_DELIMITER);
                return ScannerStatus::OK;

                vv162:
                $this->state->incrementStart();
                $this->state->setMode(Compiler::PHVOLT_MODE_RAW);
                $this->state->setWhitespaceControl(true);
                $this->token = new Token(Compiler::PHVOLT_T_CLOSE_EDELIMITER);
                return ScannerStatus::OK;
                vv164:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                        goto vv164;
                    default:
                        goto vv166;
                }
                vv166:
                {
                    $this->token = new Token(
                        Compiler::PHVOLT_T_DOUBLE,
                        substr($this->state->getRawBuffer(), $start, $this->state->getCursor() - $start)
                    );
                    return ScannerStatus::OK;
                }
                vv167:
                $this->state->incrementStart();
                {
                    $this->token = new Token(Compiler::PHVOLT_T_IDENTICAL);
                    return ScannerStatus::OK;
                }
                vv169:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv170;
                }
                vv170:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_AND);
                    return ScannerStatus::OK;
                }
                vv171:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv211;
                    default:
                        goto vv54;
                }
                vv172:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv212;
                    default:
                        goto vv54;
                }
                vv173:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv213;
                    default:
                        goto vv54;
                }
                vv174:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'H':
                    case 'h':
                        goto vv214;
                    default:
                        goto vv54;
                }
                vv175:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv215;
                    default:
                        goto vv54;
                }
                vv176:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv217;
                    default:
                        goto vv54;
                }
                vv177:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv219;
                    default:
                        goto vv54;
                }
                vv178:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv220;
                    case 'I':
                    case 'i':
                        goto vv221;
                    default:
                        goto vv54;
                }
                vv179:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv222;
                    default:
                        goto vv54;
                }
                vv180:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv224;
                    default:
                        goto vv54;
                }
                vv181:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv225;
                    case 'B':
                    case 'b':
                        goto vv226;
                    case 'C':
                    case 'c':
                        goto vv227;
                    case 'F':
                    case 'f':
                        goto vv228;
                    case 'I':
                    case 'i':
                        goto vv229;
                    case 'M':
                    case 'm':
                        goto vv230;
                    case 'R':
                    case 'r':
                        goto vv231;
                    case 'S':
                    case 's':
                        goto vv232;
                    default:
                        goto vv54;
                }
                vv182:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv233;
                    default:
                        goto vv54;
                }
                vv183:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv235;
                    default:
                        goto vv54;
                }
                vv184:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'S':
                    case 's':
                        goto vv236;
                    default:
                        goto vv54;
                }
                vv185:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv186;
                }
                vv186:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_FOR);
                    return ScannerStatus::OK;
                }
                vv187:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv237;
                    default:
                        goto vv54;
                }
                vv188:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv238;
                    default:
                        goto vv81;
                }
                vv189:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv239;
                    default:
                        goto vv54;
                }
                vv190:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv240;
                    default:
                        goto vv54;
                }
                vv191:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv192;
                }
                vv192:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_NOT);
                    return ScannerStatus::OK;
                }
                vv193:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv241;
                    default:
                        goto vv54;
                }
                vv194:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv243;
                    default:
                        goto vv54;
                }
                vv195:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv196;
                }

                vv196:
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_ODD);
                return ScannerStatus::OK;

                vv197:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv198;
                }
                vv198:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_RAW);
                    return ScannerStatus::OK;
                }
                vv199:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'U':
                    case 'u':
                        goto vv244;
                    default:
                        goto vv54;
                }
                vv200:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv245;
                    default:
                        goto vv54;
                }
                vv201:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv202;
                }
                vv202:
                {
                if ($this->state->getActiveToken() === Compiler::PHVOLT_T_DOT) {
                    $this->token = new Token(
                        Compiler::PHVOLT_T_IDENTIFIER,
                        substr($this->state->getRawBuffer(), $start, $this->state->getCursor() - $start)
                    );
                } else {
                    $this->token = new Token(Compiler::PHVOLT_T_SET);
                }

                return ScannerStatus::OK;
                }
                vv203:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv246;
                    default:
                        goto vv54;
                }
                vv204:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv247;
                    default:
                        goto vv54;
                }
                vv205:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'H':
                    case 'h':
                        goto vv249;
                    default:
                        goto vv54;
                }

                vv206:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv238;
                    default:
                        goto vv252;
                }

                vv207:
                $this->state->incrementStart();
                $this->state->setWhitespaceControl(false);
                $this->token = new Token(Compiler::PHVOLT_T_OPEN_DELIMITER);
                return ScannerStatus::OK;

                vv209:
                $this->state->incrementStart();
                $this->state->setWhitespaceControl(false);
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_OPEN_EDELIMITER);
                return ScannerStatus::OK;

                vv211:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv253;
                    default:
                        goto vv54;
                }
                vv212:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'K':
                    case 'k':
                        goto vv254;
                    default:
                        goto vv54;
                }
                vv213:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'K':
                    case 'k':
                        goto vv256;
                    default:
                        goto vv54;
                }
                vv214:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv258;
                    default:
                        goto vv54;
                }
                vv215:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv216;
                }
                vv216:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_CALL);
                    return ScannerStatus::OK;
                }
                vv217:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv218;
                }
                vv218:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_CASE);
                    return ScannerStatus::OK;
                }
                vv219:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'I':
                    case 'i':
                        goto vv260;
                    default:
                        goto vv54;
                }
                vv220:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'U':
                    case 'u':
                        goto vv261;
                    default:
                        goto vv54;
                }
                vv221:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv262;
                    default:
                        goto vv54;
                }
                vv222:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'G':
                    case 'H':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'g':
                    case 'h':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    case 'F':
                    case 'f':
                        goto vv263;
                    case 'I':
                    case 'i':
                        goto vv264;
                    default:
                        goto vv223;
                }
                vv223:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ELSE);
                    return ScannerStatus::OK;
                }
                vv224:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'Y':
                    case 'y':
                        goto vv265;
                    default:
                        goto vv54;
                }
                vv225:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'U':
                    case 'u':
                        goto vv267;
                    default:
                        goto vv54;
                }
                vv226:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv268;
                    default:
                        goto vv54;
                }
                vv227:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv269;
                    default:
                        goto vv54;
                }
                vv228:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv270;
                    default:
                        goto vv54;
                }
                vv229:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'F':
                    case 'f':
                        goto vv271;
                    default:
                        goto vv54;
                }
                vv230:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv273;
                    default:
                        goto vv54;
                }
                vv231:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv274;
                    default:
                        goto vv54;
                }
                vv232:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'W':
                    case 'w':
                        goto vv275;
                    default:
                        goto vv54;
                }
                vv233:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv234;
                }

                vv234:
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_EVEN);
                return ScannerStatus::OK;

                vv235:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv276;
                    default:
                        goto vv54;
                }
                vv236:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv277;
                    default:
                        goto vv54;
                }
                vv237:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'U':
                    case 'u':
                        goto vv279;
                    default:
                        goto vv54;
                }
                vv238:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv280;
                    default:
                        goto vv81;
                }
                vv239:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv281;
                    default:
                        goto vv54;
                }
                vv240:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv282;
                    default:
                        goto vv54;
                }
                vv241:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv242;
                }
                vv242:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_NULL);
                    return ScannerStatus::OK;
                }
                vv243:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv284;
                    default:
                        goto vv54;
                }
                vv244:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv285;
                    default:
                        goto vv54;
                }
                vv245:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv286;
                    default:
                        goto vv54;
                }
                vv246:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv287;
                    default:
                        goto vv54;
                }
                vv247:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv248;
                }
                vv248:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_TRUE);
                    return ScannerStatus::OK;
                }
                vv249:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv250;
                }
                vv250:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_WITH);
                    return ScannerStatus::OK;
                }
                vv251:
                $vvch = $this->state->incrementStart()->getStart();
                vv252:
                switch ($vvch) {
                    case ' ':
                        goto vv251;
                    case 'n':
                        goto vv288;
                    default:
                        goto vv81;
                }
                vv253:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'S':
                    case 's':
                        goto vv289;
                    default:
                        goto vv54;
                }
                vv254:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv255;
                }

                vv255:
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_BLOCK);
                return ScannerStatus::OK;

                vv256:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv257;
                }
                vv257:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_BREAK);
                    return ScannerStatus::OK;
                }
                vv258:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv259;
                }
                vv259:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_CACHE);
                    return ScannerStatus::OK;
                }
                vv260:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv290;
                    default:
                        goto vv54;
                }
                vv261:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv291;
                    default:
                        goto vv54;
                }
                vv262:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv292;
                    default:
                        goto vv54;
                }
                vv263:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv293;
                    default:
                        goto vv54;
                }
                vv264:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'F':
                    case 'f':
                        goto vv294;
                    default:
                        goto vv54;
                }
                vv265:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv266;
                }
                vv266:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_EMPTY);
                    return ScannerStatus::OK;
                }
                vv267:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv296;
                    default:
                        goto vv54;
                }
                vv268:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv297;
                    default:
                        goto vv54;
                }
                vv269:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv298;
                    case 'L':
                    case 'l':
                        goto vv299;
                    default:
                        goto vv54;
                }
                vv270:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv300;
                    default:
                        goto vv54;
                }
                vv271:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv272;
                }
                vv272:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ENDIF);
                    return ScannerStatus::OK;
                }
                vv273:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv302;
                    default:
                        goto vv54;
                }
                vv274:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'W':
                    case 'w':
                        goto vv303;
                    default:
                        goto vv54;
                }
                vv275:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'I':
                    case 'i':
                        goto vv305;
                    default:
                        goto vv54;
                }
                vv276:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'D':
                    case 'd':
                        goto vv306;
                    default:
                        goto vv54;
                }
                vv277:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv278;
                }
                vv278:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_FALSE);
                    return ScannerStatus::OK;
                }
                vv279:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'D':
                    case 'd':
                        goto vv307;
                    default:
                        goto vv54;
                }
                vv280:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv308;
                    default:
                        goto vv81;
                }
                vv281:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'B':
                    case 'b':
                        goto vv310;
                    default:
                        goto vv54;
                }
                vv282:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv283;
                }
                vv283:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_MACRO);
                    return ScannerStatus::OK;
                }
                vv284:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'I':
                    case 'i':
                        goto vv311;
                    default:
                        goto vv54;
                }
                vv285:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'N':
                    case 'n':
                        goto vv312;
                    default:
                        goto vv54;
                }
                vv286:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv314;
                    default:
                        goto vv54;
                }
                vv287:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'H':
                    case 'h':
                        goto vv316;
                    default:
                        goto vv54;
                }
                vv288:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'o':
                        goto vv318;
                    default:
                        goto vv81;
                }
                vv289:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv319;
                    default:
                        goto vv54;
                }
                vv290:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'U':
                    case 'u':
                        goto vv320;
                    default:
                        goto vv54;
                }
                vv291:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv321;
                    default:
                        goto vv54;
                }
                vv292:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'D':
                    case 'd':
                        goto vv323;
                    default:
                        goto vv54;
                }
                vv293:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv325;
                    default:
                        goto vv54;
                }
                vv294:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv295;
                }
                vv295:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ELSEIF);
                    return ScannerStatus::OK;
                }
                vv296:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv327;
                    default:
                        goto vv54;
                }
                vv297:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv328;
                    default:
                        goto vv54;
                }
                vv298:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'H':
                    case 'h':
                        goto vv329;
                    default:
                        goto vv54;
                }
                vv299:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv330;
                    default:
                        goto vv54;
                }
                vv300:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv301;
                }
                vv301:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ENDFOR);
                    return ScannerStatus::OK;
                }
                vv302:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'R':
                    case 'r':
                        goto vv332;
                    default:
                        goto vv54;
                }
                vv303:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv304;
                }
                vv304:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_ENDRAW);
                    return ScannerStatus::OK;
                }
                vv305:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'T':
                    case 't':
                        goto vv333;
                    default:
                        goto vv54;
                }
                vv306:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'S':
                    case 's':
                        goto vv334;
                    default:
                        goto vv54;
                }
                vv307:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv336;
                    default:
                        goto vv54;
                }
                vv308:
                $this->state->incrementStart();
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_NOTEQUALS);
                    return ScannerStatus::OK;
                }
                vv310:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'L':
                    case 'l':
                        goto vv338;
                    default:
                        goto vv54;
                }
                vv311:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv339;
                    default:
                        goto vv54;
                }
                vv312:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv313;
                }
                vv313:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_RETURN);
                    return ScannerStatus::OK;
                }
                vv314:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv315;
                }
                vv315:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_SCALAR);
                    return ScannerStatus::OK;
                }
                vv316:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv317;
                }
                vv317:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_SWITCH);
                    return ScannerStatus::OK;
                }
                vv318:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 't':
                        goto vv341;
                    default:
                        goto vv81;
                }
                vv319:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv343;
                    default:
                        goto vv54;
                }
                vv320:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv344;
                    default:
                        goto vv54;
                }
                vv321:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv322;
                }
                vv322:
                $this->token = new Token(
                    Compiler::PHVOLT_T_DEFAULT,
                    substr($this->state->getRawBuffer(), $start, $this->state->getCursor() - $start)
                );
                return ScannerStatus::OK;
                vv323:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv324;
                }
                vv324:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_DEFINED);
                    return ScannerStatus::OK;
                }
                vv325:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv326;
                }
                vv326:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ELSEFOR);
                    return ScannerStatus::OK;
                }
                vv327:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv346;
                    default:
                        goto vv54;
                }
                vv328:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'K':
                    case 'k':
                        goto vv347;
                    default:
                        goto vv54;
                }
                vv329:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv349;
                    default:
                        goto vv54;
                }
                vv330:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv331;
                }
                vv331:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ENDCALL);
                    return ScannerStatus::OK;
                }
                vv332:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'O':
                    case 'o':
                        goto vv351;
                    default:
                        goto vv54;
                }
                vv333:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv353;
                    default:
                        goto vv54;
                }
                vv334:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv335;
                }
                vv335:
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_EXTENDS);
                return ScannerStatus::OK;

                vv336:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv337;
                }
                vv337:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_INCLUDE);
                    return ScannerStatus::OK;
                }
                vv338:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv354;
                    default:
                        goto vv54;
                }
                vv339:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv340;
                }
                vv340:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_NUMERIC);
                    return ScannerStatus::OK;
                }
                vv341:
                $this->state->incrementStart();
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_NOTEQUALS);
                    return ScannerStatus::OK;
                }
                vv343:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'P':
                    case 'p':
                        goto vv356;
                    default:
                        goto vv54;
                }
                vv344:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv345;
                }
                vv345:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_CONTINUE);
                    return ScannerStatus::OK;
                }
                vv346:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'S':
                    case 's':
                        goto vv357;
                    default:
                        goto vv54;
                }
                vv347:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv348;
                }
                vv348:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ENDBLOCK);
                    return ScannerStatus::OK;
                }
                vv349:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv350;
                }
                vv350:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ENDCACHE);
                    return ScannerStatus::OK;
                }
                vv351:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv352;
                }
                vv352:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ENDMACRO);
                    return ScannerStatus::OK;
                }
                vv353:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'H':
                    case 'h':
                        goto vv358;
                    default:
                        goto vv54;
                }
                vv354:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv355;
                }
                vv355:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_ITERABLE);
                    return ScannerStatus::OK;
                }
                vv356:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv360;
                    default:
                        goto vv54;
                }
                vv357:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'C':
                    case 'c':
                        goto vv362;
                    default:
                        goto vv54;
                }
                vv358:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv359;
                }
                vv359:
                {
                    $this->token = new Token(Compiler::PHVOLT_T_ENDSWITCH);
                    return ScannerStatus::OK;
                }
                vv360:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv361;
                }
                vv361:
                {
                    $this->state->incrementStatementPosition();
                    $this->token = new Token(Compiler::PHVOLT_T_AUTOESCAPE);
                    return ScannerStatus::OK;
                }
                vv362:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'A':
                    case 'a':
                        goto vv363;
                    default:
                        goto vv54;
                }
                vv363:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'P':
                    case 'p':
                        goto vv364;
                    default:
                        goto vv54;
                }
                vv364:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case 'E':
                    case 'e':
                        goto vv365;
                    default:
                        goto vv54;
                }
                vv365:
                $vvch = $this->state->incrementStart()->getStart();
                switch ($vvch) {
                    case '0':
                    case '1':
                    case '2':
                    case '3':
                    case '4':
                    case '5':
                    case '6':
                    case '7':
                    case '8':
                    case '9':
                    case 'A':
                    case 'B':
                    case 'C':
                    case 'D':
                    case 'E':
                    case 'F':
                    case 'G':
                    case 'H':
                    case 'I':
                    case 'J':
                    case 'K':
                    case 'L':
                    case 'M':
                    case 'N':
                    case 'O':
                    case 'P':
                    case 'Q':
                    case 'R':
                    case 'S':
                    case 'T':
                    case 'U':
                    case 'V':
                    case 'W':
                    case 'X':
                    case 'Y':
                    case 'Z':
                    case '\\':
                    case '_':
                    case 'a':
                    case 'b':
                    case 'c':
                    case 'd':
                    case 'e':
                    case 'f':
                    case 'g':
                    case 'h':
                    case 'i':
                    case 'j':
                    case 'k':
                    case 'l':
                    case 'm':
                    case 'n':
                    case 'o':
                    case 'p':
                    case 'q':
                    case 'r':
                    case 's':
                    case 't':
                    case 'u':
                    case 'v':
                    case 'w':
                    case 'x':
                    case 'y':
                    case 'z':
                        goto vv53;
                    default:
                        goto vv366;
                }
                vv366:
                $this->state->incrementStatementPosition();
                $this->token = new Token(Compiler::PHVOLT_T_ENDAUTOESCAPE);
                return ScannerStatus::OK;
            }
        }

        return $status;
    }
}
