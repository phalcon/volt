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
use Phalcon\Volt\Compiler\Opcode as CompilerOpcode;
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
                CompilerOpcode::IGNORE->value            => null,
                CompilerOpcode::ADD->value               => $parser->phvolt_(Opcode::PLUS->value),
                CompilerOpcode::SUB->value               => $parser->phvolt_(Opcode::MINUS->value),
                CompilerOpcode::MUL->value               => $parser->phvolt_(Opcode::TIMES->value),
                CompilerOpcode::DIV->value               => $parser->phvolt_(Opcode::DIVIDE->value),
                CompilerOpcode::MOD->value               => $parser->phvolt_(Opcode::MOD->value),
                CompilerOpcode::AND->value               => $parser->phvolt_(Opcode::AND->value),
                CompilerOpcode::OR->value                => $parser->phvolt_(Opcode::OR->value),
                CompilerOpcode::IS->value                => $parser->phvolt_(Opcode::IS->value),
                CompilerOpcode::EQUALS->value            => $parser->phvolt_(Opcode::EQUALS->value),
                CompilerOpcode::NOTEQUALS->value         => $parser->phvolt_(Opcode::NOTEQUALS->value),
                CompilerOpcode::LESS->value              => $parser->phvolt_(Opcode::LESS->value),
                CompilerOpcode::GREATER->value           => $parser->phvolt_(Opcode::GREATER->value),
                CompilerOpcode::GREATEREQUAL->value      => $parser->phvolt_(Opcode::GREATEREQUAL->value),
                CompilerOpcode::LESSEQUAL->value         => $parser->phvolt_(Opcode::LESSEQUAL->value),
                CompilerOpcode::IDENTICAL->value         => $parser->phvolt_(Opcode::IDENTICAL->value),
                CompilerOpcode::NOTIDENTICAL->value      => $parser->phvolt_(Opcode::NOTIDENTICAL->value),
                CompilerOpcode::NOT->value               => $parser->phvolt_(Opcode::NOT->value),
                CompilerOpcode::DOT->value               => $parser->phvolt_(Opcode::DOT->value),
                CompilerOpcode::CONCAT->value            => $parser->phvolt_(Opcode::CONCAT->value),
                CompilerOpcode::RANGE->value             => $parser->phvolt_(Opcode::RANGE->value),
                CompilerOpcode::PIPE->value              => $parser->phvolt_(Opcode::PIPE->value),
                CompilerOpcode::COMMA->value             => $parser->phvolt_(Opcode::COMMA->value),
                CompilerOpcode::COLON->value             => $parser->phvolt_(Opcode::COLON->value),
                CompilerOpcode::QUESTION->value          => $parser->phvolt_(Opcode::QUESTION->value),
                CompilerOpcode::PARENTHESES_OPEN->value  => $parser->phvolt_(Opcode::PARENTHESES_OPEN->value),
                CompilerOpcode::PARENTHESES_CLOSE->value => $parser->phvolt_(Opcode::PARENTHESES_CLOSE->value),
                CompilerOpcode::SBRACKET_OPEN->value     => $parser->phvolt_(Opcode::SBRACKET_OPEN->value),
                CompilerOpcode::SBRACKET_CLOSE->value    => $parser->phvolt_(Opcode::SBRACKET_CLOSE->value),
                CompilerOpcode::CBRACKET_OPEN->value     => $parser->phvolt_(Opcode::CBRACKET_OPEN->value),
                CompilerOpcode::CBRACKET_CLOSE->value    => $parser->phvolt_(Opcode::CBRACKET_CLOSE->value),
                CompilerOpcode::OPEN_DELIMITER->value    => $parser->phvolt_(Opcode::OPEN_DELIMITER->value),
                CompilerOpcode::CLOSE_DELIMITER->value   => $parser->phvolt_(Opcode::CLOSE_DELIMITER->value),
                CompilerOpcode::OPEN_EDELIMITER->value   => $this->handleOpenEdelimiter($parser, $parserStatus, $state),
                CompilerOpcode::CLOSE_EDELIMITER->value  => $parser->phvolt_(Opcode::CLOSE_EDELIMITER->value),
                CompilerOpcode::NULL->value              => $parser->phvolt_(Opcode::NULL->value),
                CompilerOpcode::TRUE->value              => $parser->phvolt_(Opcode::TRUE->value),
                CompilerOpcode::FALSE->value             => $parser->phvolt_(Opcode::FALSE->value),
                CompilerOpcode::INTEGER->value           => $this->parseWithToken($parser, $token, Opcode::INTEGER),
                CompilerOpcode::DOUBLE->value            => $this->parseWithToken($parser, $token, Opcode::DOUBLE),
                CompilerOpcode::STRING->value            => $this->parseWithToken($parser, $token, Opcode::STRING),
                CompilerOpcode::IDENTIFIER->value        => $this->parseWithToken($parser, $token, Opcode::IDENTIFIER),
                CompilerOpcode::IF->value                => $this->handleIf($parser, $parserStatus, $state),
                CompilerOpcode::ELSE->value              => $state->getIfLevel() === 0 && $state->getForLevel() > 0
                    ? $parser->phvolt_(Opcode::ELSEFOR->value)
                    : $parser->phvolt_(Opcode::ELSE->value),
                CompilerOpcode::ELSEFOR->value           => $parser->phvolt_(Opcode::ELSEFOR->value),
                CompilerOpcode::ELSEIF->value            => $this->handleElseif($parser, $parserStatus, $state),
                CompilerOpcode::ENDIF->value             => $this->handleEndif($parser, $state),
                CompilerOpcode::FOR->value               => $this->handleFor($parser, $parserStatus, $state),
                CompilerOpcode::IN->value                => $parser->phvolt_(Opcode::IN->value),
                CompilerOpcode::ENDFOR->value            => $this->handleEndfor($parser, $state),
                CompilerOpcode::SWITCH->value            => $this->handleSwitch($parser, $parserStatus, $state),
                CompilerOpcode::CASE->value              => $this->handleCase($parser, $parserStatus),
                CompilerOpcode::DEFAULT->value           => $this->handleDefault(
                    $parser,
                    $parserStatus,
                    $token,
                    $state
                ),
                CompilerOpcode::ENDSWITCH->value         => $this->handleEndswitch($parser, $parserStatus, $state),
                CompilerOpcode::RAW_FRAGMENT->value      => $this->handleRawFragment(
                    $parser,
                    $parserStatus,
                    $token,
                    $state
                ),
                CompilerOpcode::SET->value               => $this->handleSet($parser, $parserStatus, $state),
                CompilerOpcode::ASSIGN->value            => $parser->phvolt_(Opcode::ASSIGN->value),
                CompilerOpcode::ADD_ASSIGN->value        => $parser->phvolt_(Opcode::ADD_ASSIGN->value),
                CompilerOpcode::SUB_ASSIGN->value        => $parser->phvolt_(Opcode::SUB_ASSIGN->value),
                CompilerOpcode::MUL_ASSIGN->value        => $parser->phvolt_(Opcode::MUL_ASSIGN->value),
                CompilerOpcode::DIV_ASSIGN->value        => $parser->phvolt_(Opcode::DIV_ASSIGN->value),
                CompilerOpcode::INCR->value              => $parser->phvolt_(Opcode::INCR->value),
                CompilerOpcode::DECR->value              => $parser->phvolt_(Opcode::DECR->value),
                CompilerOpcode::BLOCK->value             => $this->handleBlock($parser, $parserStatus, $state),
                CompilerOpcode::ENDBLOCK->value          => $this->handleEndblock($parser, $state),
                CompilerOpcode::MACRO->value             => $this->handleMacro($parser, $parserStatus, $state),
                CompilerOpcode::ENDMACRO->value          => $this->handleEndmacro($parser, $state),
                CompilerOpcode::CALL->value              => $parser->phvolt_(Opcode::CALL->value),
                CompilerOpcode::ENDCALL->value           => $parser->phvolt_(Opcode::ENDCALL->value),
                CompilerOpcode::CACHE->value             => $parser->phvolt_(Opcode::CACHE->value),
                CompilerOpcode::ENDCACHE->value          => $parser->phvolt_(Opcode::ENDCACHE->value),
                CompilerOpcode::RAW->value               => $this->handleRaw($parser, $state),
                CompilerOpcode::ENDRAW->value            => $this->handleEndraw($parser, $state),
                CompilerOpcode::INCLUDE->value           => $parser->phvolt_(Opcode::INCLUDE->value),
                CompilerOpcode::WITH->value              => $parser->phvolt_(Opcode::WITH->value),
                CompilerOpcode::DEFINED->value           => $parser->phvolt_(Opcode::DEFINED->value),
                CompilerOpcode::EMPTY->value             => $parser->phvolt_(Opcode::EMPTY->value),
                CompilerOpcode::EVEN->value              => $parser->phvolt_(Opcode::EVEN->value),
                CompilerOpcode::ODD->value               => $parser->phvolt_(Opcode::ODD->value),
                CompilerOpcode::NUMERIC->value           => $parser->phvolt_(Opcode::NUMERIC->value),
                CompilerOpcode::SCALAR->value            => $parser->phvolt_(Opcode::SCALAR->value),
                CompilerOpcode::ITERABLE->value          => $parser->phvolt_(Opcode::ITERABLE->value),
                CompilerOpcode::DO->value                => $parser->phvolt_(Opcode::DO->value),
                CompilerOpcode::RETURN->value            => $parser->phvolt_(Opcode::RETURN->value),
                CompilerOpcode::AUTOESCAPE->value        => $parser->phvolt_(Opcode::AUTOESCAPE->value),
                CompilerOpcode::ENDAUTOESCAPE->value     => $parser->phvolt_(Opcode::ENDAUTOESCAPE->value),
                CompilerOpcode::BREAK->value             => $parser->phvolt_(Opcode::BREAK->value),
                CompilerOpcode::CONTINUE->value          => $parser->phvolt_(Opcode::CONTINUE->value),
                CompilerOpcode::EXTENDS->value           => $this->handleExtends($parser, $parserStatus, $state),
                default                                  => $this->handleUnknownOpcode($parserStatus, $opcode),
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

        $newToken = new Token(CompilerOpcode::IDENTIFIER->value, $token->value);
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
