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

use Phalcon\Volt\Compiler;
use Phalcon\Volt\Exception;
use Phalcon\Volt\Scanner\Opcode;
use Phalcon\Volt\Scanner\Scanner;
use Phalcon\Volt\Scanner\ScannerStatus;
use Phalcon\Volt\Scanner\State;
use Phalcon\Volt\Scanner\Token;
use phvolt_Parser;

use function fopen;
use function sprintf;
use function strlen;
use function substr;

class Parser
{
    private bool $debug = false;

    private string $debugFile = 'volt.txt';

    private ?Token $token = null;

    public function __construct(private string $code)
    {
    }

    /**
     * @param string $templatePath
     *
     * @return array<mixed>
     * @throws Exception
     */
    public function parseView(string $templatePath): array
    {
        if (strlen($this->code) === 0) {
            return [];
        }

        $debug = null;
        if ($this->debug) {
            $debug = fopen($this->debugFile, 'w+');
        }

        $codeLength = strlen($this->code);
        $parserState = new State($this->code);
        $parserState->setActiveFile($templatePath);
        $parserStatus = new Status($parserState);
        $scanner = new Scanner($parserStatus->getState());

        $parser = new phvolt_Parser($parserStatus);
        $parser->phvolt_Trace($debug);

        $state = $parserStatus->getState();
        while (($scannerStatus = $scanner->scanForToken()) === ScannerStatus::OK) {
            $this->token = $scanner->getToken();
            $parserStatus->setToken($this->token);
            $state->setStartLength($codeLength - $state->getCursor());

            $opcode = $this->token->opcode;
            $state->setActiveToken($opcode);

            switch ($opcode) {
                case Compiler::PHVOLT_T_IGNORE:
                    break;

                case Compiler::PHVOLT_T_ADD:
                    $parser->phvolt_(Opcode::PLUS->value);
                    break;

                case Compiler::PHVOLT_T_SUB:
                    $parser->phvolt_(Opcode::MINUS->value);
                    break;

                case Compiler::PHVOLT_T_MUL:
                    $parser->phvolt_(Opcode::TIMES->value);
                    break;

                case Compiler::PHVOLT_T_DIV:
                    $parser->phvolt_(Opcode::DIVIDE->value);
                    break;

                case Compiler::PHVOLT_T_MOD:
                    $parser->phvolt_(Opcode::MOD->value);
                    break;

                case Compiler::PHVOLT_T_AND:
                    $parser->phvolt_(Opcode::AND->value);
                    break;

                case Compiler::PHVOLT_T_OR:
                    $parser->phvolt_(Opcode::OR->value);
                    break;

                case Compiler::PHVOLT_T_IS:
                    $parser->phvolt_(Opcode::IS->value);
                    break;

                case Compiler::PHVOLT_T_EQUALS:
                    $parser->phvolt_(Opcode::EQUALS->value);
                    break;

                case Compiler::PHVOLT_T_NOTEQUALS:
                    $parser->phvolt_(Opcode::NOTEQUALS->value);
                    break;

                case Compiler::PHVOLT_T_LESS:
                    $parser->phvolt_(Opcode::LESS->value);
                    break;

                case Compiler::PHVOLT_T_GREATER:
                    $parser->phvolt_(Opcode::GREATER->value);
                    break;

                case Compiler::PHVOLT_T_GREATEREQUAL:
                    $parser->phvolt_(Opcode::GREATEREQUAL->value);
                    break;

                case Compiler::PHVOLT_T_LESSEQUAL:
                    $parser->phvolt_(Opcode::LESSEQUAL->value);
                    break;

                case Compiler::PHVOLT_T_IDENTICAL:
                    $parser->phvolt_(Opcode::IDENTICAL->value);
                    break;

                case Compiler::PHVOLT_T_NOTIDENTICAL:
                    $parser->phvolt_(Opcode::NOTIDENTICAL->value);
                    break;

                case Compiler::PHVOLT_T_NOT:
                    $parser->phvolt_(Opcode::NOT->value);
                    break;

                case Compiler::PHVOLT_T_DOT:
                    $parser->phvolt_(Opcode::DOT->value);
                    break;

                case Compiler::PHVOLT_T_CONCAT:
                    $parser->phvolt_(Opcode::CONCAT->value);
                    break;

                case Compiler::PHVOLT_T_RANGE:
                    $parser->phvolt_(Opcode::RANGE->value);
                    break;

                case Compiler::PHVOLT_T_PIPE:
                    $parser->phvolt_(Opcode::PIPE->value);
                    break;

                case Compiler::PHVOLT_T_COMMA:
                    $parser->phvolt_(Opcode::COMMA->value);
                    break;

                case Compiler::PHVOLT_T_COLON:
                    $parser->phvolt_(Opcode::COLON->value);
                    break;

                case Compiler::PHVOLT_T_QUESTION:
                    $parser->phvolt_(Opcode::QUESTION->value);
                    break;

                case Compiler::PHVOLT_T_PARENTHESES_OPEN:
                    $parser->phvolt_(Opcode::PARENTHESES_OPEN->value);
                    break;

                case Compiler::PHVOLT_T_PARENTHESES_CLOSE:
                    $parser->phvolt_(Opcode::PARENTHESES_CLOSE->value);
                    break;

                case Compiler::PHVOLT_T_SBRACKET_OPEN:
                    $parser->phvolt_(Opcode::SBRACKET_OPEN->value);
                    break;

                case Compiler::PHVOLT_T_SBRACKET_CLOSE:
                    $parser->phvolt_(Opcode::SBRACKET_CLOSE->value);
                    break;

                case Compiler::PHVOLT_T_CBRACKET_OPEN:
                    $parser->phvolt_(Opcode::CBRACKET_OPEN->value);
                    break;

                case Compiler::PHVOLT_T_CBRACKET_CLOSE:
                    $parser->phvolt_(Opcode::CBRACKET_CLOSE->value);
                    break;

                case Compiler::PHVOLT_T_OPEN_DELIMITER:
                    $parser->phvolt_(Opcode::OPEN_DELIMITER->value);
                    break;

                case Compiler::PHVOLT_T_CLOSE_DELIMITER:
                    $parser->phvolt_(Opcode::CLOSE_DELIMITER->value);
                    break;

                case Compiler::PHVOLT_T_OPEN_EDELIMITER:
                    if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Child templates only may contain blocks'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::OPEN_EDELIMITER->value);
                    break;

                case Compiler::PHVOLT_T_CLOSE_EDELIMITER:
                    $parser->phvolt_(Opcode::CLOSE_EDELIMITER->value);
                    break;

                case Compiler::PHVOLT_T_NULL:
                    $parser->phvolt_(Opcode::NULL->value);
                    break;

                case Compiler::PHVOLT_T_TRUE:
                    $parser->phvolt_(Opcode::TRUE->value);
                    break;

                case Compiler::PHVOLT_T_FALSE:
                    $parser->phvolt_(Opcode::FALSE->value);
                    break;

                case Compiler::PHVOLT_T_INTEGER:
                    $this->phvoltParseWithToken(
                        $parser,
                        Compiler::PHVOLT_T_INTEGER,
                        Opcode::INTEGER
                    );
                    break;

                case Compiler::PHVOLT_T_DOUBLE:
                    $this->phvoltParseWithToken(
                        $parser,
                        Compiler::PHVOLT_T_DOUBLE,
                        Opcode::DOUBLE
                    );
                    break;

                case Compiler::PHVOLT_T_STRING:
                    $this->phvoltParseWithToken(
                        $parser,
                        Compiler::PHVOLT_T_STRING,
                        Opcode::STRING
                    );
                    break;

                case Compiler::PHVOLT_T_IDENTIFIER:
                    $this->phvoltParseWithToken(
                        $parser,
                        Compiler::PHVOLT_T_IDENTIFIER,
                        Opcode::IDENTIFIER
                    );
                    break;

                case Compiler::PHVOLT_T_IF:
                    if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Child templates only may contain blocks'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->incrementIfLevel();
                    $state->incrementBlockLevel();

                    $parser->phvolt_(Opcode::IF->value);
                    break;

                case Compiler::PHVOLT_T_ELSE:
                    if ($state->getIfLevel() === 0 && $state->getForLevel() > 0) {
                        $parser->phvolt_(Opcode::ELSEFOR->value);
                    } else {
                        $parser->phvolt_(Opcode::ELSE->value);
                    }
                    break;

                case Compiler::PHVOLT_T_ELSEFOR:
                    $parser->phvolt_(Opcode::ELSEFOR->value);
                    break;

                case Compiler::PHVOLT_T_ELSEIF:
                    if ($state->getIfLevel() === 0) {
                        $this->createErrorMessage($parserStatus, 'Unexpected ENDIF');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::ELSEIF->value);
                    break;

                case Compiler::PHVOLT_T_ENDIF:
                    $state->decrementBlockLevel();
                    $state->decrementIfLevel();
                    $parser->phvolt_(Opcode::ENDIF->value);
                    break;

                case Compiler::PHVOLT_T_FOR:
                    if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Child templates only may contain blocks'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->setOldIfLevel($state->getIfLevel());
                    $state->setIfLevel(0);
                    $state->incrementForLevel();
                    $state->incrementBlockLevel();
                    $parser->phvolt_(Opcode::FOR->value);
                    break;

                case Compiler::PHVOLT_T_IN:
                    $parser->phvolt_(Opcode::IN->value);
                    break;

                case Compiler::PHVOLT_T_ENDFOR:
                    $state->decrementBlockLevel();
                    $state->decrementForLevel();
                    $state->setIfLevel($state->getOldIfLevel());
                    $parser->phvolt_(Opcode::ENDFOR->value);
                    break;

                case Compiler::PHVOLT_T_SWITCH:
                    if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Child templates only may contain blocks'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    } elseif ($state->getSwitchLevel() > 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'A nested switch detected. There is no nested switch-case statements support'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->setSwitchLevel(1);
                    $state->incrementBlockLevel();
                    $parser->phvolt_(Opcode::SWITCH->value);
                    break;

                case Compiler::PHVOLT_T_CASE:
                    if ($state->getSwitchLevel() === 0) {
                        $this->createErrorMessage($parserStatus, 'Unexpected CASE');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::CASE->value);
                    break;

                /* only for switch-case statements */
                case Compiler::PHVOLT_T_DEFAULT:
                    if ($state->getSwitchLevel() !== 0) {
                        $parser->phvolt_(Opcode::DEFAULT->value);
                        unset($this->token);
                    } else {
                        $this->phvoltParseWithToken(
                            $parser,
                            Compiler::PHVOLT_T_IDENTIFIER,
                            Opcode::IDENTIFIER,
                        );
                    }

                    break;

                case Compiler::PHVOLT_T_ENDSWITCH:
                    if ($state->getSwitchLevel() === 0) {
                        $this->createErrorMessage($parserStatus, 'Unexpected ENDSWITCH');
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->decrementBlockLevel();
                    $state->setSwitchLevel(0);
                    $parser->phvolt_(Opcode::ENDSWITCH->value);
                    break;

                case Compiler::PHVOLT_T_RAW_FRAGMENT:
                    if ($this->token->length > 0) {
                        /** @var string $rawValue */
                        $rawValue = $this->token->value ?? '';
                        $value = trim($rawValue);
                        if ($value !== '' && $state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
                            $this->createErrorMessage(
                                $parserStatus,
                                'Child templates only may contain blocks'
                            );
                            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                            break;
                        }

                        if (!$this->phvoltIsBlankString($this->token)) {
                            $state->incrementStatementPosition();
                        }

                        $this->phvoltParseWithToken(
                            $parser,
                            Compiler::PHVOLT_T_RAW_FRAGMENT,
                            Opcode::RAW_FRAGMENT
                        );
                    }
                    break;

                case Compiler::PHVOLT_T_SET:
                    if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Child templates only may contain blocks'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $parser->phvolt_(Opcode::SET->value);
                    break;

                case Compiler::PHVOLT_T_ASSIGN:
                    $parser->phvolt_(Opcode::ASSIGN->value);
                    break;

                case Compiler::PHVOLT_T_ADD_ASSIGN:
                    $parser->phvolt_(Opcode::ADD_ASSIGN->value);
                    break;

                case Compiler::PHVOLT_T_SUB_ASSIGN:
                    $parser->phvolt_(Opcode::SUB_ASSIGN->value);
                    break;

                case Compiler::PHVOLT_T_MUL_ASSIGN:
                    $parser->phvolt_(Opcode::MUL_ASSIGN->value);
                    break;

                case Compiler::PHVOLT_T_DIV_ASSIGN:
                    $parser->phvolt_(Opcode::DIV_ASSIGN->value);
                    break;

                case Compiler::PHVOLT_T_INCR:
                    $parser->phvolt_(Opcode::INCR->value);
                    break;

                case Compiler::PHVOLT_T_DECR:
                    $parser->phvolt_(Opcode::DECR->value);
                    break;

                case Compiler::PHVOLT_T_BLOCK:
                    if ($state->getBlockLevel() > 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Embedding blocks into other blocks is not supported'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->incrementBlockLevel();
                    $parser->phvolt_(Opcode::BLOCK->value);
                    break;

                case Compiler::PHVOLT_T_ENDBLOCK:
                    $state->decrementBlockLevel();
                    $parser->phvolt_(Opcode::ENDBLOCK->value);
                    break;

                case Compiler::PHVOLT_T_MACRO:
                    if ($state->getMacroLevel() > 0) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Embedding macros into other macros is not allowed'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->incrementMacroLevel();
                    $parser->phvolt_(Opcode::MACRO->value);
                    break;

                case Compiler::PHVOLT_T_ENDMACRO:
                    $state->decrementMacroLevel();
                    $parser->phvolt_(Opcode::ENDMACRO->value);
                    break;

                case Compiler::PHVOLT_T_CALL:
                    $parser->phvolt_(Opcode::CALL->value);
                    break;

                case Compiler::PHVOLT_T_ENDCALL:
                    $parser->phvolt_(Opcode::ENDCALL->value);
                    break;

                case Compiler::PHVOLT_T_CACHE:
                    $parser->phvolt_(Opcode::CACHE->value);
                    break;

                case Compiler::PHVOLT_T_ENDCACHE:
                    $parser->phvolt_(Opcode::ENDCACHE->value);
                    break;

                case Compiler::PHVOLT_T_RAW:
                    $parser->phvolt_(Opcode::RAW->value);
                    $state->incrementForcedRawState();
                    break;

                case Compiler::PHVOLT_T_ENDRAW:
                    $parser->phvolt_(Opcode::ENDRAW->value);
                    $state->decrementForcedRawState();
                    break;

                case Compiler::PHVOLT_T_INCLUDE:
                    $parser->phvolt_(Opcode::INCLUDE->value);
                    break;

                case Compiler::PHVOLT_T_WITH:
                    $parser->phvolt_(Opcode::WITH->value);
                    break;

                case Compiler::PHVOLT_T_DEFINED:
                    $parser->phvolt_(Opcode::DEFINED->value);
                    break;

                case Compiler::PHVOLT_T_EMPTY:
                    $parser->phvolt_(Opcode::EMPTY->value);
                    break;

                case Compiler::PHVOLT_T_EVEN:
                    $parser->phvolt_(Opcode::EVEN->value);
                    break;

                case Compiler::PHVOLT_T_ODD:
                    $parser->phvolt_(Opcode::ODD->value);
                    break;

                case Compiler::PHVOLT_T_NUMERIC:
                    $parser->phvolt_(Opcode::NUMERIC->value);
                    break;

                case Compiler::PHVOLT_T_SCALAR:
                    $parser->phvolt_(Opcode::SCALAR->value);
                    break;

                case Compiler::PHVOLT_T_ITERABLE:
                    $parser->phvolt_(Opcode::ITERABLE->value);
                    break;

                case Compiler::PHVOLT_T_DO:
                    $parser->phvolt_(Opcode::DO->value);
                    break;

                case Compiler::PHVOLT_T_RETURN:
                    $parser->phvolt_(Opcode::RETURN->value);
                    break;

                case Compiler::PHVOLT_T_AUTOESCAPE:
                    $parser->phvolt_(Opcode::AUTOESCAPE->value);
                    break;

                case Compiler::PHVOLT_T_ENDAUTOESCAPE:
                    $parser->phvolt_(Opcode::ENDAUTOESCAPE->value);
                    break;

                case Compiler::PHVOLT_T_BREAK:
                    $parser->phvolt_(Opcode::BREAK->value);
                    break;

                case Compiler::PHVOLT_T_CONTINUE:
                    $parser->phvolt_(Opcode::CONTINUE->value);
                    break;

                case Compiler::PHVOLT_T_EXTENDS:
                    if ($state->getStatementPosition() !== 1) {
                        $this->createErrorMessage(
                            $parserStatus,
                            'Extends statement must be placed at the first line in the template'
                        );
                        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                        break;
                    }

                    $state->setExtendsMode(1);
                    $parser->phvolt_(Opcode::EXTENDS->value);
                    break;

                default:
                    $this->createErrorMessage(
                        $parserStatus,
                        sprintf('Scanner: unknown opcode %d', $opcode)
                    );
                    $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
                    break;
            }

            if ($parserStatus->getStatus() !== Status::PHVOLT_PARSING_OK) {
                break;
            }

            $state->setEnd($state->getStart());
        }

        if (
            $scannerStatus === ScannerStatus::ERR ||
            $scannerStatus === ScannerStatus::IMPOSSIBLE
        ) {
            throw new Exception($this->createScannerErrorMessage($parserStatus));
        } elseif ($scannerStatus === ScannerStatus::EOF) {
            $parser->phvolt_(0);
        }

        $state->setStartLength(0);
        $state->setActiveToken(null);

        if ($parserStatus->getStatus() !== Status::PHVOLT_PARSING_OK) {
            throw new Exception($parserStatus->getSyntaxError() ?? '');
        }

        return $parser->getOutput();
    }

    /**
     * @param Status $parserStatus
     * @param string $message
     *
     * @return void
     */
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

    private function createScannerErrorMessage(Status $parserStatus): string
    {
        $state = $parserStatus->getState();
        if ($state->getStartLength() > 0) {
            if ($state->getStartLength() > 16) {
                $part = substr($state->getRawBuffer(), $state->getCursor(), 16);
                $error = sprintf(
                    "Scanning error before '%s...' in %s on line %d",
                    $part,
                    $state->getActiveFile(),
                    $state->getActiveLine(),
                );
            } else {
                $error = sprintf(
                    "Scanning error before '%s' in %s on line %d",
                    $state->getStart(),
                    $state->getActiveFile(),
                    $state->getActiveLine(),
                );
            }
        } else {
            $error = sprintf(
                "Scanning error near to EOF in %s",
                $state->getActiveFile(),
            );
        }

        return $error;
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    private function phvoltIsBlankString(Token $token): bool
    {
        /** @var string $marker */
        $marker = $token->value ?? '';
        $len = strlen($marker);

        for ($i = 0; $i < $len; $i++) {
            $ch = $marker[$i];
            if ($ch !== ' ' && $ch !== "\t" && $ch !== "\n" && $ch !== "\r" && $ch !== "\v") {
                return false;
            }
        }

        return true;
    }

    private function phvoltParseWithToken(phvolt_Parser $parser, int $opcode, Opcode $parserCode): void
    {
        $newToken = new Token($opcode, $this->token?->value);

        $this->token = $newToken;

        $parser->phvolt_($parserCode->value, $newToken);
    }
}
