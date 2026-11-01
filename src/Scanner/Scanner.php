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

use Phalcon\Volt\Compiler\Opcode;

use function ltrim;
use function max;
use function preg_match;
use function rtrim;
use function str_contains;
use function strlen;
use function strpos;
use function strspn;
use function strtolower;
use function substr;
use function substr_count;

/**
 * Hand-written port of cphalcon's re2c scanner
 * (ext/phalcon/mvc/view/engine/volt/scanner.re).
 *
 * The scanner has three modes:
 *
 *  - RAW: text outside tags is buffered until `{{`, `{%` or the end of input
 *    and emitted as one RAW_FRAGMENT. `{# ... #}` comments are swallowed
 *    without flushing the buffer, so `a{# c #}b` is the single fragment "ab".
 *  - VERBATIM: after `{% verbatim %}`, everything up to `{% endverbatim %}` is
 *    captured literally.
 *  - CODE: inside tags, one token per call. This is the re2c part, and it keeps
 *    re2c's two tie-break rules:
 *      1. the longest match wins - DOUBLE is tried before INTEGER, `is not`
 *         beats `is`, `===` beats `==`, `-%}` beats `-`;
 *      2. on equal length the rule listed first wins, and every keyword is
 *         listed before IDENTIFIER - so `if` is IF but `iffy` is IDENTIFIER.
 *
 * Input is treated as NUL-terminated, like the C buffer: a NUL byte ends the
 * scan exactly as the end of the string does.
 *
 * Whitespace control (`{%-`, `{{-`, `-%}`, `-}}`) trims exactly space, \t,
 * \n, \r and \v. A left-trim requested by `-%}` / `-}}` applies to the next
 * non-empty fragment, so it crosses comments; any opening tag cancels it.
 */
final class Scanner
{
    /**
     * Keywords whose scanner.re action increments the statement position.
     * The driver uses the position to enforce "extends must come first".
     *
     * @var array<string, true>
     */
    private const COUNTS_AS_STATEMENT = [
        'autoescape'    => true,
        'block'         => true,
        'break'         => true,
        'call'          => true,
        'continue'      => true,
        'defined'       => true,
        'do'            => true,
        'empty'         => true,
        'endautoescape' => true,
        'endverbatim'   => true,
        'even'          => true,
        'extends'       => true,
        'for'           => true,
        'if'            => true,
        'include'       => true,
        'is'            => true,
        'iterable'      => true,
        'macro'         => true,
        'not'           => true,
        'numeric'       => true,
        'odd'           => true,
        'scalar'        => true,
        'switch'        => true,
        'verbatim'      => true,
    ];

    private const DOUBLE_OR_INTEGER = '/[0-9]+(?:\.[0-9]+)?/A';

    private const ENDVERBATIM = '/\{%-?[ \t\r\n]*endverbatim(?![a-zA-Z0-9_])/Ai';

    private const IDENTIFIER = '/\\\\?[a-zA-Z_][a-zA-Z0-9_\\\\]*/A';

    private const IS_NOT_ONE_SPACE = '/is not/Ai';

    private const IS_NOT_SPACES = '/is +not/A';

    /**
     * Keywords, matched case-insensitively. `cache` / `endcache` are
     * deliberately absent: phalcon/volt removed the cache statement, so they
     * scan as identifiers.
     *
     * @var array<string, Opcode>
     */
    private const KEYWORDS = [
        'and'           => Opcode::AND,
        'autoescape'    => Opcode::AUTOESCAPE,
        'block'         => Opcode::BLOCK,
        'break'         => Opcode::BREAK,
        'call'          => Opcode::CALL,
        'case'          => Opcode::CASE,
        'continue'      => Opcode::CONTINUE,
        'default'       => Opcode::DEFAULT,
        'defined'       => Opcode::DEFINED,
        'do'            => Opcode::DO,
        'else'          => Opcode::ELSE,
        'elsefor'       => Opcode::ELSEFOR,
        'elseif'        => Opcode::ELSEIF,
        'empty'         => Opcode::EMPTY,
        'endautoescape' => Opcode::ENDAUTOESCAPE,
        'endblock'      => Opcode::ENDBLOCK,
        'endcall'       => Opcode::ENDCALL,
        'endfor'        => Opcode::ENDFOR,
        'endif'         => Opcode::ENDIF,
        'endmacro'      => Opcode::ENDMACRO,
        'endswitch'     => Opcode::ENDSWITCH,
        'endverbatim'   => Opcode::ENDVERBATIM,
        'even'          => Opcode::EVEN,
        'extends'       => Opcode::EXTENDS,
        'false'         => Opcode::FALSE,
        'for'           => Opcode::FOR,
        'if'            => Opcode::IF,
        'in'            => Opcode::IN,
        'include'       => Opcode::INCLUDE,
        'is'            => Opcode::IS,
        'iterable'      => Opcode::ITERABLE,
        'macro'         => Opcode::MACRO,
        'not'           => Opcode::NOT,
        'null'          => Opcode::NULL,
        'numeric'       => Opcode::NUMERIC,
        'odd'           => Opcode::ODD,
        'or'            => Opcode::OR,
        'return'        => Opcode::RETURN,
        'scalar'        => Opcode::SCALAR,
        'set'           => Opcode::SET,
        'switch'        => Opcode::SWITCH,
        'true'          => Opcode::TRUE,
        'verbatim'      => Opcode::VERBATIM,
        'with'          => Opcode::WITH,
    ];

    /**
     * Punctuation and operators, longest first within each leading character
     * so the first hit is the longest match.
     *
     * @var array<string, list<array{0: string, 1: Opcode}>>
     */
    private const OPERATORS = [
        '!' => [['!==', Opcode::NOTIDENTICAL], ['!=', Opcode::NOTEQUALS], ['!', Opcode::NOT]],
        '%' => [['%}', Opcode::CLOSE_DELIMITER], ['%', Opcode::MOD]],
        '(' => [['(', Opcode::PARENTHESES_OPEN]],
        ')' => [[')', Opcode::PARENTHESES_CLOSE]],
        '*' => [['*=', Opcode::MUL_ASSIGN], ['*', Opcode::MUL]],
        '+' => [['++', Opcode::INCR], ['+=', Opcode::ADD_ASSIGN], ['+', Opcode::ADD]],
        ',' => [[',', Opcode::COMMA]],
        '-' => [
            ['-%}', Opcode::CLOSE_DELIMITER],
            ['-}}', Opcode::CLOSE_EDELIMITER],
            ['--', Opcode::DECR],
            ['-=', Opcode::SUB_ASSIGN],
            ['-', Opcode::SUB],
        ],
        '.' => [['..', Opcode::RANGE], ['.', Opcode::DOT]],
        '/' => [['/=', Opcode::DIV_ASSIGN], ['/', Opcode::DIV]],
        ':' => [[':', Opcode::COLON]],
        '<' => [['<=', Opcode::LESSEQUAL], ['<>', Opcode::NOTEQUALS], ['<', Opcode::LESS]],
        '=' => [['===', Opcode::IDENTICAL], ['==', Opcode::EQUALS], ['=', Opcode::ASSIGN]],
        '>' => [['>=', Opcode::GREATEREQUAL], ['>', Opcode::GREATER]],
        '?' => [['?', Opcode::QUESTION]],
        '[' => [['[', Opcode::SBRACKET_OPEN]],
        ']' => [[']', Opcode::SBRACKET_CLOSE]],
        '{' => [
            ['{%-', Opcode::OPEN_DELIMITER],
            ['{{-', Opcode::OPEN_EDELIMITER],
            ['{%', Opcode::OPEN_DELIMITER],
            ['{{', Opcode::OPEN_EDELIMITER],
            ['{', Opcode::CBRACKET_OPEN],
        ],
        '|' => [['|', Opcode::PIPE]],
        '}' => [['}}', Opcode::CLOSE_EDELIMITER], ['}', Opcode::CBRACKET_CLOSE]],
        '~' => [['~', Opcode::CONCAT]],
    ];

    private const STRING_DOUBLE = '/"(?:\\\\[^\n\x00]|[^\x00\\\\"])*"/A';

    private const STRING_SINGLE = "/'(?:\\\\[^\\n\\x00]|[^\\x00\\\\'])*'/A";

    /**
     * Characters removed by whitespace control (phvolt_ltrim/phvolt_rtrim).
     */
    private const TRIM = " \t\n\r\v";

    private readonly string $code;

    /**
     * Offset of the first NUL byte, or the length of the input: everything
     * from here on is invisible, as it is to the C scanner.
     */
    private readonly int $end;

    private Token $token;

    public function __construct(private State $state)
    {
        $this->code  = $state->getCode();
        $nul         = strpos($this->code, "\0");
        $this->end   = $nul === false ? strlen($this->code) : $nul;
        $this->token = new Token();
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * Scans the next token. On OK the token is available from getToken(); on
     * ERR the cursor has moved past the offending byte, as in C.
     */
    public function scanForToken(): ScannerStatus
    {
        return match ($this->state->getMode()) {
            Mode::VERBATIM           => $this->scanVerbatim(),
            Mode::CODE               => $this->scanCode(),
            Mode::RAW, Mode::COMMENT => $this->scanRaw(),
        };
    }

    private function consumeRaw(int $from, int $to): void
    {
        $text = substr($this->code, $from, $to - $from);

        $this->state->appendToRawFragment($text);
        $this->state->incrementActiveLine(substr_count($text, "\n"));
        $this->state->setCursor($to);
    }

    private function emit(Opcode $opcode, int $next, ?string $value = null): ScannerStatus
    {
        $this->state->setCursor($next);
        $this->token = new Token($opcode->value, $value);

        return ScannerStatus::OK;
    }

    /**
     * Ends RAW mode at a tag (or the end of input): emits the buffered text as
     * a RAW_FRAGMENT, or IGNORE when nothing was buffered. The cursor stays on
     * the tag, which CODE mode then scans.
     */
    private function flushRaw(int $tag): ScannerStatus
    {
        $this->state->setMode(Mode::CODE);

        $fragment = $this->state->getRawFragment();
        if ($fragment === '') {
            $this->token = new Token(Opcode::IGNORE->value);

            return ScannerStatus::OK;
        }

        if ($this->state->getWhitespaceControl()) {
            $fragment = ltrim($fragment, self::TRIM);
            $this->state->setWhitespaceControl(false);
        }

        $isTag = $tag < $this->end;
        if ($isTag && $tag + 2 < $this->end && $this->code[$tag + 2] === '-') {
            $fragment = rtrim($fragment, self::TRIM);
        }

        $this->state->setRawFragment('');
        $this->token = new Token(Opcode::RAW_FRAGMENT->value, $fragment);

        return ScannerStatus::OK;
    }

    /**
     * Length of `is not` at $cursor: `is not` with one space in any case, or
     * `is  not` with several spaces in lower case only. 0 when neither matches.
     */
    private function matchIsNot(int $cursor): int
    {
        return max(
            $this->matchLength(self::IS_NOT_ONE_SPACE, $cursor),
            $this->matchLength(self::IS_NOT_SPACES, $cursor)
        );
    }

    /**
     * Length of an anchored match at $cursor that ends before the end of the
     * input, or 0.
     */
    private function matchLength(string $pattern, int $cursor): int
    {
        if (preg_match($pattern, $this->code, $matches, 0, $cursor) !== 1) {
            return 0;
        }

        $length = strlen($matches[0]);

        return $cursor + $length <= $this->end ? $length : 0;
    }

    /**
     * Offset of the next `{{`, `{%` or `{#` at or after $from, or the end.
     */
    private function nextTag(int $from): int
    {
        $position = $from;

        while (true) {
            $brace = strpos($this->code, '{', $position);
            if ($brace === false || $brace >= $this->end) {
                return $this->end;
            }

            $next = $brace + 1 < $this->end ? $this->code[$brace + 1] : "\0";
            if ($next === '%' || $next === '{' || $next === '#') {
                return $brace;
            }

            $position = $brace + 1;
        }
    }

    /**
     * CODE mode: one re2c token.
     */
    private function scanCode(): ScannerStatus
    {
        $cursor = $this->state->getCursor();

        if ($cursor >= $this->end) {
            return ScannerStatus::EOF;
        }

        $char = $this->code[$cursor];

        if ($char === ' ' || $char === "\t" || $char === "\r") {
            $length = strspn($this->code, " \t\r", $cursor, $this->end - $cursor);

            return $this->emit(Opcode::IGNORE, $cursor + $length);
        }

        if ($char === "\n") {
            $this->state->incrementActiveLine();

            return $this->emit(Opcode::IGNORE, $cursor + 1);
        }

        if ($char >= '0' && $char <= '9') {
            $number = substr($this->code, $cursor, $this->matchLength(self::DOUBLE_OR_INTEGER, $cursor));
            $opcode = str_contains($number, '.') ? Opcode::DOUBLE : Opcode::INTEGER;

            return $this->emit($opcode, $cursor + strlen($number), $number);
        }

        if ($char === '"' || $char === "'") {
            return $this->scanString($cursor, $char === '"' ? self::STRING_DOUBLE : self::STRING_SINGLE);
        }

        if (
            preg_match(self::IDENTIFIER, $this->code, $matches, 0, $cursor) === 1
            && $cursor + strlen($matches[0]) <= $this->end
        ) {
            return $this->scanWord($cursor, $matches[0]);
        }

        foreach (self::OPERATORS[$char] ?? [] as [$operator, $opcode]) {
            $length = strlen($operator);
            if ($cursor + $length <= $this->end && substr($this->code, $cursor, $length) === $operator) {
                return $this->scanOperator($cursor + $length, $operator, $opcode);
            }
        }

        // [^] - any other byte is a scanning error; the cursor moves past it
        $this->state->setCursor($cursor + 1);

        return ScannerStatus::ERR;
    }

    /**
     * Applies the mode / whitespace-control side effects of the delimiter
     * rules (scanner.re lines 566-628) and emits the operator token.
     */
    private function scanOperator(int $next, string $operator, Opcode $opcode): ScannerStatus
    {
        switch ($operator) {
            case '{%':
            case '{%-':
                $this->state->setWhitespaceControl(false);
                break;

            case '{{':
            case '{{-':
                $this->state->setWhitespaceControl(false);
                $this->state->incrementStatementPosition();
                break;

            case '%}':
            case '-%}':
                if ($this->state->isVerbatim()) {
                    $this->state->setMode(Mode::VERBATIM);
                    $this->state->setVerbatim(false);
                } else {
                    $this->state->setMode(Mode::RAW);
                }
                if ($operator === '-%}') {
                    $this->state->setWhitespaceControl(true);
                }
                break;

            case '}}':
            case '-}}':
                $this->state->setMode(Mode::RAW);
                $this->state->setVerbatim(false);
                if ($operator === '-}}') {
                    $this->state->setWhitespaceControl(true);
                }
                break;
        }

        return $this->emit($opcode, $next);
    }

    /**
     * RAW mode (scanner.re lines 88-155): buffer text up to the next tag or
     * the end of input, swallowing comments.
     */
    private function scanRaw(): ScannerStatus
    {
        $cursor = $this->state->getCursor();
        $stop   = $this->nextTag($cursor);

        if ($stop > $cursor) {
            $this->consumeRaw($cursor, $stop);
        }

        if ($stop >= $this->end || $this->code[$stop + 1] !== '#') {
            return $this->flushRaw($stop);
        }

        return $this->skipComment($stop);
    }

    /**
     * A quoted string. The value keeps its escape sequences verbatim. A string
     * that is never closed - or whose last byte is a lone backslash - does not
     * match, which makes its opening quote a scanning error.
     */
    private function scanString(int $cursor, string $pattern): ScannerStatus
    {
        if (
            preg_match($pattern, $this->code, $matches, 0, $cursor) !== 1
            || $cursor + strlen($matches[0]) > $this->end
        ) {
            $this->state->setCursor($cursor + 1);

            return ScannerStatus::ERR;
        }

        $literal = $matches[0];

        return $this->emit(Opcode::STRING, $cursor + strlen($literal), substr($literal, 1, -1));
    }

    /**
     * VERBATIM mode (scanner.re lines 157-260): capture text literally up to
     * `{% endverbatim %}` (optional `-`, spaces and any letter case). The
     * cursor stays on that tag so CODE mode tokenizes it next.
     */
    private function scanVerbatim(): ScannerStatus
    {
        $cursor   = $this->state->getCursor();
        $position = $cursor;
        $stop     = $this->end;

        while (($open = strpos($this->code, '{%', $position)) !== false && $open < $this->end) {
            if (
                preg_match(self::ENDVERBATIM, $this->code, $matches, 0, $open) === 1
                && $open + strlen($matches[0]) <= $this->end
            ) {
                $stop = $open;
                break;
            }

            $position = $open + 1;
        }

        if ($stop > $cursor) {
            $this->consumeRaw($cursor, $stop);
        }

        $this->state->setMode(Mode::CODE);
        $fragment = $this->state->getRawFragment();

        if ($fragment === '') {
            if ($stop >= $this->end) {
                return ScannerStatus::EOF;
            }

            $this->token = new Token(Opcode::IGNORE->value);

            return ScannerStatus::OK;
        }

        $this->state->setRawFragment('');
        $this->token = new Token(Opcode::RAW_FRAGMENT->value, $fragment);

        return ScannerStatus::OK;
    }

    /**
     * A keyword or an identifier (they tie on length and the keyword wins),
     * plus the two `is not` spellings, which are longer than `is`.
     */
    private function scanWord(int $cursor, string $word): ScannerStatus
    {
        $keyword = strtolower($word);

        if ($keyword === 'is') {
            $isNot = $this->matchIsNot($cursor);
            if ($isNot > 0) {
                $this->state->incrementStatementPosition();

                return $this->emit(Opcode::NOTEQUALS, $cursor + $isNot);
            }
        }

        $next   = $cursor + strlen($word);
        $opcode = self::KEYWORDS[$keyword] ?? null;

        if ($opcode === null) {
            return $this->emit(Opcode::IDENTIFIER, $next, $word);
        }

        $afterDot = $this->state->getActiveToken() === Opcode::DOT->value;

        // `.set` and `.is` are property names, not keywords
        if (($opcode === Opcode::SET || $opcode === Opcode::IS) && $afterDot) {
            return $this->emit(Opcode::IDENTIFIER, $next, $word);
        }

        if (isset(self::COUNTS_AS_STATEMENT[$keyword])) {
            $this->state->incrementStatementPosition();
        }

        if ($opcode === Opcode::VERBATIM) {
            $this->state->setVerbatim(true);
        }

        // `default` keeps its spelling: the driver may turn it back into an identifier
        return $this->emit($opcode, $next, $opcode === Opcode::DEFAULT ? $word : null);
    }

    /**
     * Swallows a `{# ... #}` comment starting at $open. Emits IGNORE when it
     * is closed, EOF when the input ends first; the raw buffer is untouched in
     * both cases, so text around a comment joins into one fragment. A
     * comment's own `#` may close it: `{#}` is complete.
     */
    private function skipComment(int $open): ScannerStatus
    {
        $close = strpos($this->code, '#}', $open + 1);

        if ($close === false || $close + 1 >= $this->end) {
            $this->state->incrementActiveLine(
                substr_count($this->code, "\n", $open, $this->end - $open)
            );

            return ScannerStatus::EOF;
        }

        $this->state->incrementActiveLine(substr_count($this->code, "\n", $open, $close - $open));
        $this->state->setCursor($close + 2);
        $this->token = new Token(Opcode::IGNORE->value);

        return ScannerStatus::OK;
    }
}
