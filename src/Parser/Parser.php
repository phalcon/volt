<?php

declare(strict_types=1);

namespace Phalcon\Volt\Parser;

use Phalcon\Volt\Compiler;
use Phalcon\Volt\Scanner\Opcode;
use Phalcon\Volt\Scanner\Scanner;
use Phalcon\Volt\Scanner\State;
use Phalcon\Volt\Scanner\Token;

class Parser
{
    private ?Token $token = null;

    public function __construct(private string $code)
    {
    }

    public function parseView(string $templatePath): array
    {
        if (strlen($this->code) === 0) {
            return [];
        }

        $debug = fopen('log.txt', 'w+');

        $state = new State($this->code);
        $parserStatus = new Status($state);
        $scanner = new Scanner($parserStatus->getState());

        $parser = new \phvolt_Parser($parserStatus);
        $parser->phvolt_Trace($debug);

        while (0 <= $scannerStatus = $scanner->scanForToken()) {
            $this->token = $scanner->getToken();
            $parserStatus->setToken($this->token);

            $opcode = $this->token->getOpcode();
            $state->setActiveToken($opcode);

            switch ($opcode) {
                case Compiler::PHVOLT_T_IGNORE:
                    break;

                case Compiler::PHVOLT_T_ADD:
                    $parser->phvolt_(Opcode::PHVOLT_PLUS);
                    break;

                case Compiler::PHVOLT_T_SUB:
                    $parser->phvolt_(Opcode::PHVOLT_MINUS);
                    break;

                case Compiler::PHVOLT_T_MUL:
                    $parser->phvolt_(Opcode::PHVOLT_TIMES);
                    break;

                case Compiler::PHVOLT_T_DIV:
                    $parser->phvolt_(Opcode::PHVOLT_DIVIDE);
                    break;

                case Compiler::PHVOLT_T_MOD:
                    $parser->phvolt_(Opcode::PHVOLT_MOD);
                    break;

                case Compiler::PHVOLT_T_AND:
                    $parser->phvolt_(Opcode::PHVOLT_AND);
                    break;

                case Compiler::PHVOLT_T_OR:
                    $parser->phvolt_(Opcode::PHVOLT_OR);
                    break;

                case Compiler::PHVOLT_T_IS:
                    $parser->phvolt_(Opcode::PHVOLT_IS);
                    break;

                case Compiler::PHVOLT_T_EQUALS:
                    $parser->phvolt_(Opcode::PHVOLT_EQUALS);
                    break;

                case Compiler::PHVOLT_T_NOTEQUALS:
                    $parser->phvolt_(Opcode::PHVOLT_NOTEQUALS);
                    break;

                case Compiler::PHVOLT_T_LESS:
                    $parser->phvolt_(Opcode::PHVOLT_LESS);
                    break;

                case Compiler::PHVOLT_T_GREATER:
                    $parser->phvolt_(Opcode::PHVOLT_GREATER);
                    break;

                case Compiler::PHVOLT_T_GREATEREQUAL:
                    $parser->phvolt_(Opcode::PHVOLT_GREATEREQUAL);
                    break;

                case Compiler::PHVOLT_T_LESSEQUAL:
                    $parser->phvolt_(Opcode::PHVOLT_LESSEQUAL);
                    break;

                case Compiler::PHVOLT_T_IDENTICAL:
                    $parser->phvolt_(Opcode::PHVOLT_IDENTICAL);
                    break;

                case Compiler::PHVOLT_T_NOTIDENTICAL:
                    $parser->phvolt_(Opcode::PHVOLT_NOTIDENTICAL);
                    break;

                case Compiler::PHVOLT_T_NOT:
                    $parser->phvolt_(Opcode::PHVOLT_NOT);
                    break;

                case Compiler::PHVOLT_T_DOT:
                    $parser->phvolt_(Opcode::PHVOLT_DOT);
                    break;

                case Compiler::PHVOLT_T_CONCAT:
                    $parser->phvolt_(Opcode::PHVOLT_CONCAT);
                    break;

                case Compiler::PHVOLT_T_RANGE:
                    $parser->phvolt_(Opcode::PHVOLT_RANGE);
                    break;

                case Compiler::PHVOLT_T_PIPE:
                    $parser->phvolt_(Opcode::PHVOLT_PIPE);
                    break;

                case Compiler::PHVOLT_T_COMMA:
                    $parser->phvolt_(Opcode::PHVOLT_COMMA);
                    break;

                case Compiler::PHVOLT_T_COLON:
                    $parser->phvolt_(Opcode::PHVOLT_COLON);
                    break;

                case Compiler::PHVOLT_T_QUESTION:
                    $parser->phvolt_(Opcode::PHVOLT_QUESTION);
                    break;

                case Compiler::PHVOLT_T_PARENTHESES_OPEN:
                    $parser->phvolt_(Opcode::PHVOLT_PARENTHESES_OPEN);
                    break;

                case Compiler::PHVOLT_T_PARENTHESES_CLOSE:
                    $parser->phvolt_(Opcode::PHVOLT_PARENTHESES_CLOSE);
                    break;

                case Compiler::PHVOLT_T_SBRACKET_OPEN:
                    $parser->phvolt_(Opcode::PHVOLT_SBRACKET_OPEN);
                    break;

                case Compiler::PHVOLT_T_SBRACKET_CLOSE:
                    $parser->phvolt_(Opcode::PHVOLT_SBRACKET_CLOSE);
                    break;

                case Compiler::PHVOLT_T_CBRACKET_OPEN:
                    $parser->phvolt_(Opcode::PHVOLT_CBRACKET_OPEN);
                    break;

                case Compiler::PHVOLT_T_CBRACKET_CLOSE:
                    $parser->phvolt_(Opcode::PHVOLT_CBRACKET_CLOSE);
                    break;

                case Compiler::PHVOLT_T_OPEN_DELIMITER:
                    $parser->phvolt_(Opcode::PHVOLT_OPEN_DELIMITER);
                    break;

                case Compiler::PHVOLT_T_CLOSE_DELIMITER:
                    $parser->phvolt_(Opcode::PHVOLT_CLOSE_DELIMITER);
                    break;

                case Compiler::PHVOLT_T_OPEN_EDELIMITER:
                    if ($state->extendsMode === 1 && $state->blockLevel == 0) {
                        $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::PHVOLT_OPEN_EDELIMITER);
                    break;

                case Compiler::PHVOLT_T_CLOSE_EDELIMITER:
                    $parser->phvolt_(Opcode::PHVOLT_CLOSE_EDELIMITER);
                    break;

                case Compiler::PHVOLT_T_NULL:
                    $parser->phvolt_(Opcode::PHVOLT_NULL);
                    break;

                case Compiler::PHVOLT_T_TRUE:
                    $parser->phvolt_(Opcode::PHVOLT_TRUE);
                    break;

                case Compiler::PHVOLT_T_FALSE:
                    $parser->phvolt_(Opcode::PHVOLT_FALSE);
                    break;

                case Compiler::PHVOLT_T_INTEGER:
                    $this->phvolt_parse_with_token($parser, Compiler::PHVOLT_T_INTEGER, Opcode::PHVOLT_INTEGER);
                    break;

                case Compiler::PHVOLT_T_DOUBLE:
                    $this->phvolt_parse_with_token($parser, Compiler::PHVOLT_T_DOUBLE, Opcode::PHVOLT_DOUBLE);
                    break;

                case Compiler::PHVOLT_T_STRING:
                    $this->phvolt_parse_with_token($parser, Compiler::PHVOLT_T_STRING, Opcode::PHVOLT_STRING);
                    break;

                case Compiler::PHVOLT_T_IDENTIFIER:
                    $this->phvolt_parse_with_token($parser, Compiler::PHVOLT_T_IDENTIFIER, Opcode::PHVOLT_IDENTIFIER);
                    break;

                case Compiler::PHVOLT_T_IF:
                    if ($state->extendsMode === 1 && $state->blockLevel == 0) {
                        $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->ifLevel++;
                    $state->blockLevel++;

                    $parser->phvolt_(Opcode::PHVOLT_IF);
                    break;

                case Compiler::PHVOLT_T_ELSE:
                    if ($state->ifLevel === 0 && $state->forLevel > 0) {
                        $parser->phvolt_(Opcode::PHVOLT_ELSEFOR);
                    } else {
                        $parser->phvolt_(Opcode::PHVOLT_ELSE);
                    }
                    break;

                case Compiler::PHVOLT_T_ELSEFOR:
                    $parser->phvolt_(Opcode::PHVOLT_ELSEFOR);
                    break;

                case Compiler::PHVOLT_T_ELSEIF:
                    if ($state->ifLevel === 0) {
                        $this->createErrorMessage($parserStatus, 'Unexpected ENDIF');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::PHVOLT_ELSEIF);
                    break;

                case Compiler::PHVOLT_T_ENDIF:
                    $state->blockLevel--;
                    $state->ifLevel--;
                    $parser->phvolt_(Opcode::PHVOLT_ENDIF);
                    break;

                case Compiler::PHVOLT_T_FOR:
                    if ($state->extendsMode === 1 && $state->blockLevel == 0) {
                        $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->oldIfLevel = $state->ifLevel;
                    $state->ifLevel = 0;
                    $state->forLevel++;
                    $state->blockLevel++;
                    $parser->phvolt_(Opcode::PHVOLT_FOR);
                    break;

                case Compiler::PHVOLT_T_IN:
                    $parser->phvolt_(Opcode::PHVOLT_IN);
                    break;

                case Compiler::PHVOLT_T_ENDFOR:
                    $state->blockLevel--;
                    $state->forLevel--;
                    $state->ifLevel = $state->oldIfLevel;
                    $parser->phvolt_(Opcode::PHVOLT_ENDFOR);
                    break;

                case Compiler::PHVOLT_T_SWITCH:
                    if ($state->extendsMode === 1 && $state->blockLevel == 0) {
                        $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    } elseif ($state->switchLevel > 0) {
                        $this->createErrorMessage($parserStatus, 'A nested switch detected. There is no nested switch-case statements support');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->switchLevel = 1;
                    $state->blockLevel++;
                    $parser->phvolt_(Opcode::PHVOLT_SWITCH);
                    break;

                case Compiler::PHVOLT_T_CASE:
                    if ($state->switchLevel === 0) {
                        $this->createErrorMessage($parserStatus, 'Unexpected CASE');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::PHVOLT_CASE);
                    break;

                /* only for switch-case statements */
                case Compiler::PHVOLT_T_DEFAULT:
                    if ($state->switchLevel !== 0) {
                        $parser->phvolt_(Opcode::PHVOLT_DEFAULT);
                        unset($this->token);
                        break;
                    }

                    $this->phvolt_parse_with_token($parser, Compiler::PHVOLT_T_IDENTIFIER, Opcode::PHVOLT_DEFAULT);
                    break;

                case Compiler::PHVOLT_T_ENDSWITCH:
                    if ($state->switchLevel === 0) {
                        $this->createErrorMessage($parserStatus, 'Unexpected ENDSWITCH');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->blockLevel--;
                    $state->switchLevel = 0;
                    $parser->phvolt_(Opcode::PHVOLT_ENDSWITCH);
                    break;

                case Compiler::PHVOLT_T_RAW_FRAGMENT:
                    if ($this->token !== null) {
                        if ($state->extendsMode === 1 && $state->blockLevel === 0) {
                            $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
                            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                            break;
                        }

                        if (!$this->phvolt_is_blank_string($this->token)) {
                            $state->statementPosition++;
                        }

                        $this->phvolt_parse_with_token($parser, Compiler::PHVOLT_T_RAW_FRAGMENT, Opcode::PHVOLT_RAW_FRAGMENT);
                    }
                    break;

                case Compiler::PHVOLT_T_SET:
                    if ($state->extendsMode === 1 && $state->blockLevel === 0) {
                        $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::PHVOLT_SET);
                    break;

                case Compiler::PHVOLT_T_ASSIGN:
                    $parser->phvolt_(Opcode::PHVOLT_ASSIGN);
                    break;

                case Compiler::PHVOLT_T_ADD_ASSIGN:
                    $parser->phvolt_(Opcode::PHVOLT_ADD_ASSIGN);
                    break;

                case Compiler::PHVOLT_T_SUB_ASSIGN:
                    $parser->phvolt_(Opcode::PHVOLT_SUB_ASSIGN);
                    break;

                case Compiler::PHVOLT_T_MUL_ASSIGN:
                    $parser->phvolt_(Opcode::PHVOLT_MUL_ASSIGN);
                    break;

                case Compiler::PHVOLT_T_DIV_ASSIGN:
                    $parser->phvolt_(Opcode::PHVOLT_DIV_ASSIGN);
                    break;

                case Compiler::PHVOLT_T_INCR:
                    $parser->phvolt_(Opcode::PHVOLT_INCR);
                    break;

                case Compiler::PHVOLT_T_DECR:
                    $parser->phvolt_(Opcode::PHVOLT_DECR);
                    break;

                case Compiler::PHVOLT_T_BLOCK:
                    if ($state->blockLevel > 0) {
                        $this->createErrorMessage($parserStatus, 'Embedding blocks into other blocks is not supported');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->blockLevel++;
                    $parser->phvolt_(Opcode::PHVOLT_BLOCK);
                    break;

                case Compiler::PHVOLT_T_ENDBLOCK:
                    $state->blockLevel--;
                    $parser->phvolt_(Opcode::PHVOLT_ENDBLOCK);
                    break;

                case Compiler::PHVOLT_T_MACRO:
                    if ($state->macroLevel > 0) {
                        $this->createErrorMessage($parserStatus, 'Embedding macros into other macros is not allowed');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->macroLevel++;
                    $parser->phvolt_(Opcode::PHVOLT_MACRO);
                    break;

                case Compiler::PHVOLT_T_ENDMACRO:
                    $state->macroLevel--;
                    $parser->phvolt_(Opcode::PHVOLT_ENDMACRO);
                    break;

                case Compiler::PHVOLT_T_CALL:
                    $parser->phvolt_(Opcode::PHVOLT_CALL);
                    break;

                case Compiler::PHVOLT_T_ENDCALL:
                    $parser->phvolt_(Opcode::PHVOLT_ENDCALL);
                    break;

                case Compiler::PHVOLT_T_CACHE:
                    $parser->phvolt_(Opcode::PHVOLT_CACHE);
                    break;

                case Compiler::PHVOLT_T_ENDCACHE:
                    $parser->phvolt_(Opcode::PHVOLT_ENDCACHE);
                    break;

                case Compiler::PHVOLT_T_RAW:
                    $parser->phvolt_(Opcode::PHVOLT_RAW);
                    $state->forcedRawState++;
                    break;

                case Compiler::PHVOLT_T_ENDRAW:
                    $parser->phvolt_(Opcode::PHVOLT_ENDRAW);
                    $state->forcedRawState--;
                    break;

                case Compiler::PHVOLT_T_INCLUDE:
                    $parser->phvolt_(Opcode::PHVOLT_INCLUDE);
                    break;

                case Compiler::PHVOLT_T_WITH:
                    $parser->phvolt_(Opcode::PHVOLT_WITH);
                    break;

                case Compiler::PHVOLT_T_DEFINED:
                    $parser->phvolt_(Opcode::PHVOLT_DEFINED);
                    break;

                case Compiler::PHVOLT_T_EMPTY:
                    $parser->phvolt_(Opcode::PHVOLT_EMPTY);
                    break;

                case Compiler::PHVOLT_T_EVEN:
                    $parser->phvolt_(Opcode::PHVOLT_EVEN);
                    break;

                case Compiler::PHVOLT_T_ODD:
                    $parser->phvolt_(Opcode::PHVOLT_ODD);
                    break;

                case Compiler::PHVOLT_T_NUMERIC:
                    $parser->phvolt_(Opcode::PHVOLT_NUMERIC);
                    break;

                case Compiler::PHVOLT_T_SCALAR:
                    $parser->phvolt_(Opcode::PHVOLT_SCALAR);
                    break;

                case Compiler::PHVOLT_T_ITERABLE:
                    $parser->phvolt_(Opcode::PHVOLT_ITERABLE);
                    break;

                case Compiler::PHVOLT_T_DO:
                    $parser->phvolt_(Opcode::PHVOLT_DO);
                    break;

                case Compiler::PHVOLT_T_RETURN:
                    $parser->phvolt_(Opcode::PHVOLT_RETURN);
                    break;

                case Compiler::PHVOLT_T_AUTOESCAPE:
                    $parser->phvolt_(Opcode::PHVOLT_AUTOESCAPE);
                    break;

                case Compiler::PHVOLT_T_ENDAUTOESCAPE:
                    $parser->phvolt_(Opcode::PHVOLT_ENDAUTOESCAPE);
                    break;

                case Compiler::PHVOLT_T_BREAK:
                    $parser->phvolt_(Opcode::PHVOLT_BREAK);
                    break;

                case Compiler::PHVOLT_T_CONTINUE:
                    $parser->phvolt_(Opcode::PHVOLT_CONTINUE);
                    break;

                case Compiler::PHVOLT_T_EXTENDS:
                    if ($state->statementPosition !== 1) {
                        $this->createErrorMessage($parserStatus, 'Extends statement must be placed at the first line in the template');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->extendsMode = 1;
                    $parser->phvolt_(Opcode::PHVOLT_EXTENDS);
                    break;

                    default:
                        $this->createErrorMessage($parserStatus, sprintf('Scanner: unknown opcode %d', $opcode));
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
            }

            if ($parserStatus->getStatus() !== Status::PHVOLT_PARSING_OK) {
                break;
            }

            $state->setEnd($state->getStart());
        }

        if ($scannerStatus === Scanner::PHVOLT_SCANNER_RETCODE_EOF) {
            $parser->phvolt_(0);
        }

        return $parser->getOutput();
    }

    private function createErrorMessage(Status $parserStatus, string $message): void
    {
        $length = 128 + strlen($parserStatus->getState()->getActiveFile());
        $str = sprintf(
            "%s in %s on line %d",
            $message,
            $parserStatus->getState()->getActiveFile(),
            $parserStatus->getState()->getActiveLine(),
        );

        $parserStatus->setSyntaxError(substr($str, 0, $length));
    }

    private function phvolt_parse_with_token(\phvolt_Parser $parser, int $opcode, int $parserCode): void
    {
        $newToken = new Token();
        $newToken->setOpcode($opcode);
        $newToken->setValue($this->token->getValue());
        $newToken->setFreeFlag(true);

        $this->token = $newToken;

        $parser->phvolt_($parserCode, $newToken);
    }

    private function phvolt_is_blank_string(Token $token): bool
    {
        $marker = $token->getValue();
        $len = strlen($marker);

        for ($i = 0; $i < $len; $i++) {
            $ch = $marker[$i];
            if ($ch !== ' ' && $ch !== "\t" && $ch !== "\n" && $ch !== "\r" && $ch !== "\v") {
                return false;
            }
        }

        return true;
    }
}
