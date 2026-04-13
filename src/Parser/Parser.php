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

    /**
     * @param string $code
     * @param string $templatePath
     *
     * @return array<mixed>
     * @throws Exception
     */
    public function parse(string $code, string $templatePath = 'eval code'): array
    {
        if (strlen($code) === 0) {
            return [];
        }

        $debugHandle = null;
        if ($this->debug) {
            $debugHandle = fopen($this->debugFile, 'w+');
        }

        $codeLength  = strlen($code);
        $parserState = new State($code);
        $parserState->setActiveFile($templatePath);
        $parserStatus = new Status($parserState);
        $scanner      = new Scanner($parserStatus->getState());

        $parser = new phvolt_Parser($parserStatus);
        $parser->phvolt_Trace($debugHandle);

        $state        = $parserStatus->getState();
        $scannerStatus = ScannerStatus::OK;

        while (($scannerStatus = $scanner->scanForToken()) === ScannerStatus::OK) {
            $token = $scanner->getToken();
            $parserStatus->setToken($token);
            $state->setStartLength($codeLength - $state->getCursor());

            $opcode = $token->opcode;
            $state->setActiveToken($opcode);

            match ($opcode) {
                Compiler::PHVOLT_T_IGNORE            => null,
                Compiler::PHVOLT_T_ADD               => $parser->phvolt_(Opcode::PLUS->value),
                Compiler::PHVOLT_T_SUB               => $parser->phvolt_(Opcode::MINUS->value),
                Compiler::PHVOLT_T_MUL               => $parser->phvolt_(Opcode::TIMES->value),
                Compiler::PHVOLT_T_DIV               => $parser->phvolt_(Opcode::DIVIDE->value),
                Compiler::PHVOLT_T_MOD               => $parser->phvolt_(Opcode::MOD->value),
                Compiler::PHVOLT_T_AND               => $parser->phvolt_(Opcode::AND->value),
                Compiler::PHVOLT_T_OR                => $parser->phvolt_(Opcode::OR->value),
                Compiler::PHVOLT_T_IS                => $parser->phvolt_(Opcode::IS->value),
                Compiler::PHVOLT_T_EQUALS            => $parser->phvolt_(Opcode::EQUALS->value),
                Compiler::PHVOLT_T_NOTEQUALS         => $parser->phvolt_(Opcode::NOTEQUALS->value),
                Compiler::PHVOLT_T_LESS              => $parser->phvolt_(Opcode::LESS->value),
                Compiler::PHVOLT_T_GREATER           => $parser->phvolt_(Opcode::GREATER->value),
                Compiler::PHVOLT_T_GREATEREQUAL      => $parser->phvolt_(Opcode::GREATEREQUAL->value),
                Compiler::PHVOLT_T_LESSEQUAL         => $parser->phvolt_(Opcode::LESSEQUAL->value),
                Compiler::PHVOLT_T_IDENTICAL         => $parser->phvolt_(Opcode::IDENTICAL->value),
                Compiler::PHVOLT_T_NOTIDENTICAL      => $parser->phvolt_(Opcode::NOTIDENTICAL->value),
                Compiler::PHVOLT_T_NOT               => $parser->phvolt_(Opcode::NOT->value),
                Compiler::PHVOLT_T_DOT               => $parser->phvolt_(Opcode::DOT->value),
                Compiler::PHVOLT_T_CONCAT            => $parser->phvolt_(Opcode::CONCAT->value),
                Compiler::PHVOLT_T_RANGE             => $parser->phvolt_(Opcode::RANGE->value),
                Compiler::PHVOLT_T_PIPE              => $parser->phvolt_(Opcode::PIPE->value),
                Compiler::PHVOLT_T_COMMA             => $parser->phvolt_(Opcode::COMMA->value),
                Compiler::PHVOLT_T_COLON             => $parser->phvolt_(Opcode::COLON->value),
                Compiler::PHVOLT_T_QUESTION          => $parser->phvolt_(Opcode::QUESTION->value),
                Compiler::PHVOLT_T_PARENTHESES_OPEN  => $parser->phvolt_(Opcode::PARENTHESES_OPEN->value),
                Compiler::PHVOLT_T_PARENTHESES_CLOSE => $parser->phvolt_(Opcode::PARENTHESES_CLOSE->value),
                Compiler::PHVOLT_T_SBRACKET_OPEN     => $parser->phvolt_(Opcode::SBRACKET_OPEN->value),
                Compiler::PHVOLT_T_SBRACKET_CLOSE    => $parser->phvolt_(Opcode::SBRACKET_CLOSE->value),
                Compiler::PHVOLT_T_CBRACKET_OPEN     => $parser->phvolt_(Opcode::CBRACKET_OPEN->value),
                Compiler::PHVOLT_T_CBRACKET_CLOSE    => $parser->phvolt_(Opcode::CBRACKET_CLOSE->value),
                Compiler::PHVOLT_T_OPEN_DELIMITER    => $parser->phvolt_(Opcode::OPEN_DELIMITER->value),
                Compiler::PHVOLT_T_CLOSE_DELIMITER   => $parser->phvolt_(Opcode::CLOSE_DELIMITER->value),
                Compiler::PHVOLT_T_OPEN_EDELIMITER   => $this->handleOpenEdelimiter($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_CLOSE_EDELIMITER  => $parser->phvolt_(Opcode::CLOSE_EDELIMITER->value),
                Compiler::PHVOLT_T_NULL              => $parser->phvolt_(Opcode::NULL->value),
                Compiler::PHVOLT_T_TRUE              => $parser->phvolt_(Opcode::TRUE->value),
                Compiler::PHVOLT_T_FALSE             => $parser->phvolt_(Opcode::FALSE->value),
                Compiler::PHVOLT_T_INTEGER           => $this->parseWithToken($parser, $token, Opcode::INTEGER),
                Compiler::PHVOLT_T_DOUBLE            => $this->parseWithToken($parser, $token, Opcode::DOUBLE),
                Compiler::PHVOLT_T_STRING            => $this->parseWithToken($parser, $token, Opcode::STRING),
                Compiler::PHVOLT_T_IDENTIFIER        => $this->parseWithToken($parser, $token, Opcode::IDENTIFIER),
                Compiler::PHVOLT_T_IF                => $this->handleIf($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_ELSE              => $state->getIfLevel() === 0 && $state->getForLevel() > 0
                    ? $parser->phvolt_(Opcode::ELSEFOR->value)
                    : $parser->phvolt_(Opcode::ELSE->value),
                Compiler::PHVOLT_T_ELSEFOR           => $parser->phvolt_(Opcode::ELSEFOR->value),
                Compiler::PHVOLT_T_ELSEIF            => $this->handleElseif($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_ENDIF             => $this->handleEndif($parser, $state),
                Compiler::PHVOLT_T_FOR               => $this->handleFor($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_IN                => $parser->phvolt_(Opcode::IN->value),
                Compiler::PHVOLT_T_ENDFOR            => $this->handleEndfor($parser, $state),
                Compiler::PHVOLT_T_SWITCH            => $this->handleSwitch($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_CASE              => $this->handleCase($parser, $parserStatus),
                Compiler::PHVOLT_T_DEFAULT           => $this->handleDefault($parser, $parserStatus, $token, $state),
                Compiler::PHVOLT_T_ENDSWITCH         => $this->handleEndswitch($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_RAW_FRAGMENT      => $this->handleRawFragment(
                    $parser,
                    $parserStatus,
                    $token,
                    $state
                ),
                Compiler::PHVOLT_T_SET               => $this->handleSet($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_ASSIGN            => $parser->phvolt_(Opcode::ASSIGN->value),
                Compiler::PHVOLT_T_ADD_ASSIGN        => $parser->phvolt_(Opcode::ADD_ASSIGN->value),
                Compiler::PHVOLT_T_SUB_ASSIGN        => $parser->phvolt_(Opcode::SUB_ASSIGN->value),
                Compiler::PHVOLT_T_MUL_ASSIGN        => $parser->phvolt_(Opcode::MUL_ASSIGN->value),
                Compiler::PHVOLT_T_DIV_ASSIGN        => $parser->phvolt_(Opcode::DIV_ASSIGN->value),
                Compiler::PHVOLT_T_INCR              => $parser->phvolt_(Opcode::INCR->value),
                Compiler::PHVOLT_T_DECR              => $parser->phvolt_(Opcode::DECR->value),
                Compiler::PHVOLT_T_BLOCK             => $this->handleBlock($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_ENDBLOCK          => $this->handleEndblock($parser, $state),
                Compiler::PHVOLT_T_MACRO             => $this->handleMacro($parser, $parserStatus, $state),
                Compiler::PHVOLT_T_ENDMACRO          => $this->handleEndmacro($parser, $state),
                Compiler::PHVOLT_T_CALL              => $parser->phvolt_(Opcode::CALL->value),
                Compiler::PHVOLT_T_ENDCALL           => $parser->phvolt_(Opcode::ENDCALL->value),
                Compiler::PHVOLT_T_CACHE             => $parser->phvolt_(Opcode::CACHE->value),
                Compiler::PHVOLT_T_ENDCACHE          => $parser->phvolt_(Opcode::ENDCACHE->value),
                Compiler::PHVOLT_T_RAW               => $this->handleRaw($parser, $state),
                Compiler::PHVOLT_T_ENDRAW            => $this->handleEndraw($parser, $state),
                Compiler::PHVOLT_T_INCLUDE           => $parser->phvolt_(Opcode::INCLUDE->value),
                Compiler::PHVOLT_T_WITH              => $parser->phvolt_(Opcode::WITH->value),
                Compiler::PHVOLT_T_DEFINED           => $parser->phvolt_(Opcode::DEFINED->value),
                Compiler::PHVOLT_T_EMPTY             => $parser->phvolt_(Opcode::EMPTY->value),
                Compiler::PHVOLT_T_EVEN              => $parser->phvolt_(Opcode::EVEN->value),
                Compiler::PHVOLT_T_ODD               => $parser->phvolt_(Opcode::ODD->value),
                Compiler::PHVOLT_T_NUMERIC           => $parser->phvolt_(Opcode::NUMERIC->value),
                Compiler::PHVOLT_T_SCALAR            => $parser->phvolt_(Opcode::SCALAR->value),
                Compiler::PHVOLT_T_ITERABLE          => $parser->phvolt_(Opcode::ITERABLE->value),
                Compiler::PHVOLT_T_DO                => $parser->phvolt_(Opcode::DO->value),
                Compiler::PHVOLT_T_RETURN            => $parser->phvolt_(Opcode::RETURN->value),
                Compiler::PHVOLT_T_AUTOESCAPE        => $parser->phvolt_(Opcode::AUTOESCAPE->value),
                Compiler::PHVOLT_T_ENDAUTOESCAPE     => $parser->phvolt_(Opcode::ENDAUTOESCAPE->value),
                Compiler::PHVOLT_T_BREAK             => $parser->phvolt_(Opcode::BREAK->value),
                Compiler::PHVOLT_T_CONTINUE          => $parser->phvolt_(Opcode::CONTINUE->value),
                Compiler::PHVOLT_T_EXTENDS           => $this->handleExtends($parser, $parserStatus, $state),
                default                              => $this->handleUnknownOpcode($parserStatus, $opcode),
            };

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

    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;

        return $this;
    }

    public function setDebugFile(string $debugFile): static
    {
        $this->debugFile = $debugFile;

        return $this;
    }

    private function createErrorMessage(Status $parserStatus, string $message): void
    {
        $length = 128 + strlen($parserStatus->getState()->getActiveFile());
        $str    = sprintf(
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

                return sprintf(
                    "Scanning error before '%s...' in %s on line %d",
                    $part,
                    $state->getActiveFile(),
                    $state->getActiveLine(),
                );
            }

            return sprintf(
                "Scanning error before '%s' in %s on line %d",
                $state->getStart(),
                $state->getActiveFile(),
                $state->getActiveLine(),
            );
        }

        return sprintf(
            "Scanning error near to EOF in %s",
            $state->getActiveFile(),
        );
    }

    private function handleBlock(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getBlockLevel() > 0) {
            $this->createErrorMessage($parserStatus, 'Embedding blocks into other blocks is not supported');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $state->incrementBlockLevel();
        $parser->phvolt_(Opcode::BLOCK->value);
    }

    private function handleCase(phvolt_Parser $parser, Status $parserStatus): void
    {
        if ($parserStatus->getState()->getSwitchLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Unexpected CASE');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $parser->phvolt_(Opcode::CASE->value);
    }

    private function handleDefault(
        phvolt_Parser $parser,
        Status $parserStatus,
        Token $token,
        State $state
    ): void {
        if ($state->getSwitchLevel() !== 0) {
            $parser->phvolt_(Opcode::DEFAULT->value);

            return;
        }

        $newToken = new Token(Compiler::PHVOLT_T_IDENTIFIER, $token->value);
        $parser->phvolt_(Opcode::IDENTIFIER->value, $newToken);
    }

    private function handleElseif(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getIfLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Unexpected ENDIF');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $parser->phvolt_(Opcode::ELSEIF->value);
    }

    private function handleEndblock(phvolt_Parser $parser, State $state): void
    {
        $state->decrementBlockLevel();
        $parser->phvolt_(Opcode::ENDBLOCK->value);
    }

    private function handleEndfor(phvolt_Parser $parser, State $state): void
    {
        $state->decrementBlockLevel();
        $state->decrementForLevel();
        $state->setIfLevel($state->getOldIfLevel());
        $parser->phvolt_(Opcode::ENDFOR->value);
    }

    private function handleEndif(phvolt_Parser $parser, State $state): void
    {
        $state->decrementBlockLevel();
        $state->decrementIfLevel();
        $parser->phvolt_(Opcode::ENDIF->value);
    }

    private function handleEndmacro(phvolt_Parser $parser, State $state): void
    {
        $state->decrementMacroLevel();
        $parser->phvolt_(Opcode::ENDMACRO->value);
    }

    private function handleEndraw(phvolt_Parser $parser, State $state): void
    {
        $parser->phvolt_(Opcode::ENDRAW->value);
        $state->decrementForcedRawState();
    }

    private function handleEndswitch(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getSwitchLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Unexpected ENDSWITCH');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $state->decrementBlockLevel();
        $state->setSwitchLevel(0);
        $parser->phvolt_(Opcode::ENDSWITCH->value);
    }

    private function handleExtends(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getStatementPosition() !== 1) {
            $this->createErrorMessage(
                $parserStatus,
                'Extends statement must be placed at the first line in the template'
            );
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $state->setExtendsMode(1);
        $parser->phvolt_(Opcode::EXTENDS->value);
    }

    private function handleFor(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $state->setOldIfLevel($state->getIfLevel());
        $state->setIfLevel(0);
        $state->incrementForLevel();
        $state->incrementBlockLevel();
        $parser->phvolt_(Opcode::FOR->value);
    }

    private function handleIf(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $state->incrementIfLevel();
        $state->incrementBlockLevel();
        $parser->phvolt_(Opcode::IF->value);
    }

    private function handleMacro(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getMacroLevel() > 0) {
            $this->createErrorMessage($parserStatus, 'Embedding macros into other macros is not allowed');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $state->incrementMacroLevel();
        $parser->phvolt_(Opcode::MACRO->value);
    }

    private function handleOpenEdelimiter(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $parser->phvolt_(Opcode::OPEN_EDELIMITER->value);
    }

    private function handleRaw(phvolt_Parser $parser, State $state): void
    {
        $parser->phvolt_(Opcode::RAW->value);
        $state->incrementForcedRawState();
    }

    private function handleRawFragment(
        phvolt_Parser $parser,
        Status $parserStatus,
        Token $token,
        State $state
    ): void {
        if ($token->length === 0) {
            return;
        }

        $value = trim((string)$token->value);

        if ($value !== '' && $state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        if (!$this->isBlankString($token)) {
            $state->incrementStatementPosition();
        }

        $this->parseWithToken($parser, $token, Opcode::RAW_FRAGMENT);
    }

    private function handleSet(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $parser->phvolt_(Opcode::SET->value);
    }

    private function handleSwitch(phvolt_Parser $parser, Status $parserStatus, State $state): void
    {
        if ($state->getExtendsMode() === 1 && $state->getBlockLevel() === 0) {
            $this->createErrorMessage($parserStatus, 'Child templates only may contain blocks');
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        if ($state->getSwitchLevel() > 0) {
            $this->createErrorMessage(
                $parserStatus,
                'A nested switch detected. There is no nested switch-case statements support'
            );
            $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);

            return;
        }

        $state->setSwitchLevel(1);
        $state->incrementBlockLevel();
        $parser->phvolt_(Opcode::SWITCH->value);
    }

    private function handleUnknownOpcode(Status $parserStatus, int $opcode): void
    {
        $this->createErrorMessage($parserStatus, sprintf('Scanner: unknown opcode %d', $opcode));
        $parserStatus->setStatus(Status::PHVOLT_PARSING_FAILED);
    }

    private function isBlankString(Token $token): bool
    {
        $marker = (string)$token->value;
        $len    = strlen($marker);

        for ($i = 0; $i < $len; $i++) {
            $ch = $marker[$i];
            if ($ch !== ' ' && $ch !== "\t" && $ch !== "\n" && $ch !== "\r" && $ch !== "\v") {
                return false;
            }
        }

        return true;
    }

    private function parseWithToken(phvolt_Parser $parser, Token $token, Opcode $parserCode): void
    {
        $newToken = new Token($token->opcode, $token->value);

        $parser->phvolt_($parserCode->value, $newToken);
    }
}
