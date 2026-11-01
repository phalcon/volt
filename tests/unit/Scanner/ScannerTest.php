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

namespace Phalcon\Tests\Unit\Scanner;

use Phalcon\Volt\Compiler\Opcode;
use Phalcon\Volt\Scanner\Scanner;
use Phalcon\Volt\Scanner\ScannerStatus;
use Phalcon\Volt\Scanner\State;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The scanner's token contract, rule by rule from cphalcon's scanner.re:
 * the opcode, the value and the line counter after each token.
 */
final class ScannerTest extends TestCase
{
    private const DELIMITERS = ['OPEN_DELIMITER', 'CLOSE_DELIMITER', 'OPEN_EDELIMITER', 'CLOSE_EDELIMITER'];

    /**
     * @return array<string, array{0: string, 1: list<array{0: string, 1: ?string}>}>
     */
    public static function isNotProvider(): array
    {
        $isNot  = ['NOTEQUALS', null];
        $a      = ['IDENTIFIER', 'a'];
        $b      = ['IDENTIFIER', 'b'];

        return [
            'one space'          => ['{{ a is not b }}', [$a, $isNot, $b]],
            'one space upper'    => ['{{ a IS NOT b }}', [$a, $isNot, $b]],
            'two spaces'         => ['{{ a is  not b }}', [$a, $isNot, $b]],
            'two spaces upper'   => ['{{ a IS  NOT b }}', [$a, ['IS', null], ['NOT', null], $b]],
            'longest match wins' => ['{{ a is nothing }}', [$a, $isNot, ['IDENTIFIER', 'hing']]],
            'plain is'           => ['{{ a is b }}', [$a, ['IS', null], $b]],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function scanningErrorProvider(): array
    {
        return [
            'unknown byte'          => ['{{ $ }}', 4],
            'unterminated string'   => ['{{ "abc', 4],
            'trailing backslash'    => ["{{ 'abc\\", 4],
            'backslash newline'     => ["{{ 'a\\\nb' }}", 4],
            'lone backslash'        => ['{{ \\ }}', 4],
            'vertical tab in code'  => ["{{ \x0B }}", 4],
        ];
    }

    public function testCacheIsAnIdentifier(): void
    {
        $this->assertSame(
            [['IDENTIFIER', 'cache'], ['IDENTIFIER', 'endcache']],
            $this->codeTokens('{{ cache endcache }}')
        );
    }

    public function testCommentContainingZeroIsSkipped(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'x', 1], ['EOF', null, 1]],
            $this->scan('{# 0 #}x')
        );
    }

    public function testCommentDoesNotFlushRawBuffer(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'ab', 1], ['EOF', null, 1]],
            $this->scan('a{# c #}b')
        );
    }

    public function testCommentNewlinesAreCounted(): void
    {
        $this->assertSame(
            [
                ['OPEN_EDELIMITER', null, 3],
                ['IDENTIFIER', 'c', 3],
                ['CLOSE_EDELIMITER', null, 3],
                ['EOF', null, 3],
            ],
            $this->scan("{# a\n\nb #}{{ c }}")
        );
    }

    public function testCommentOwnHashCanCloseIt(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'x', 1], ['EOF', null, 1]],
            $this->scan('{#}x')
        );
    }

    public function testDashAfterOpenDelimiterIsWhitespaceControl(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'a'], ['OPEN_EDELIMITER', null], ['INTEGER', '1'], ['CLOSE_EDELIMITER', null]],
            $this->codeTokens('a {{-1}}', [])
        );
    }

    public function testDefaultKeepsItsSpelling(): void
    {
        $this->assertSame([['DEFAULT', 'DeFault']], $this->codeTokens('{{ DeFault }}'));
    }

    public function testDoubleStarAndSlashAreTwoTokens(): void
    {
        $this->assertSame(
            [
                ['IDENTIFIER', 'a'], ['MUL', null], ['MUL', null],
                ['IDENTIFIER', 'b'], ['DIV', null], ['DIV', null],
                ['IDENTIFIER', 'c'],
            ],
            $this->codeTokens('{{ a ** b // c }}')
        );
    }

    public function testEmptyVerbatimHasNoFragment(): void
    {
        $this->assertSame(
            [['VERBATIM', null], ['ENDVERBATIM', null]],
            $this->codeTokens('{% verbatim %}{% endverbatim %}', ['OPEN_DELIMITER', 'CLOSE_DELIMITER'])
        );
    }

    /**
     * @param list<array{0: string, 1: ?string}> $expected
     */
    #[DataProvider('isNotProvider')]
    public function testIsNotSpellings(string $code, array $expected): void
    {
        $this->assertSame($expected, $this->codeTokens($code));
    }

    public function testKeywordsAreCaseInsensitive(): void
    {
        $this->assertSame(
            [['IF', null], ['IDENTIFIER', 'a'], ['ENDIF', null], ['TRUE', null], ['AND', null]],
            $this->codeTokens('{% IF a %}{% EndIf %}{{ True AnD }}')
        );
    }

    public function testLeftTrimCrossesAComment(): void
    {
        $this->assertSame(
            [['IDENTIFIER', 'a'], ['RAW_FRAGMENT', 'x']],
            $this->codeTokens('{{ a -}} {# c #}   x')
        );
    }

    public function testLongerIdentifierBeatsKeyword(): void
    {
        $this->assertSame(
            [['IDENTIFIER', 'iffy'], ['IDENTIFIER', 'nullable'], ['IDENTIFIER', 'if\\x']],
            $this->codeTokens('{{ iffy nullable if\\x }}')
        );
    }

    public function testNewlinesInCodeAreCounted(): void
    {
        $this->assertSame(
            [['IDENTIFIER', 'a', 1], ['IDENTIFIER', 'b', 3]],
            $this->lineTokens("{{ a\n\nb }}", ['IDENTIFIER'])
        );
    }

    public function testNewlinesInsideStringsAreNotCounted(): void
    {
        $this->assertSame(
            [['STRING', "a\nb", 1], ['IDENTIFIER', 'c', 2]],
            $this->lineTokens("{{ 'a\nb' }}\n{{ c }}", ['STRING', 'IDENTIFIER'])
        );
    }

    public function testNulByteEndsTheScan(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'x', 1], ['EOF', null, 1]],
            $this->scan("x\0y{{ z }}")
        );
    }

    public function testNumbers(): void
    {
        $this->assertSame(
            [
                ['DOUBLE', '1.5'],
                ['INTEGER', '1'], ['DOT', null],
                ['INTEGER', '1'], ['RANGE', null], ['INTEGER', '2'],
                ['INTEGER', '007'],
            ],
            $this->codeTokens('{{ 1.5 1. 1..2 007 }}')
        );
    }

    public function testOpeningTagCancelsPendingLeftTrim(): void
    {
        $this->assertSame(
            [['IDENTIFIER', 'a'], ['IDENTIFIER', 'b'], ['RAW_FRAGMENT', '   c']],
            $this->codeTokens('{{ a -}}{{ b }}   c')
        );
    }

    public function testOperatorsTakeTheLongestMatch(): void
    {
        $this->assertSame(
            [
                ['IDENTICAL', null], ['NOTIDENTICAL', null], ['NOTEQUALS', null], ['NOTEQUALS', null],
                ['LESSEQUAL', null], ['GREATEREQUAL', null], ['INCR', null], ['DECR', null],
                ['ADD_ASSIGN', null], ['SUB_ASSIGN', null], ['MUL_ASSIGN', null], ['DIV_ASSIGN', null],
                ['RANGE', null], ['EQUALS', null], ['ASSIGN', null], ['NOT', null],
            ],
            $this->codeTokens('{{ === !== != <> <= >= ++ -- += -= *= /= .. == = ! }}')
        );
    }

    public function testRawTextIsOneFragment(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', "a\nb", 2], ['EOF', null, 2]],
            $this->scan("a\nb")
        );
    }

    #[DataProvider('scanningErrorProvider')]
    public function testScanningErrorMovesPastTheOffendingByte(string $code, int $cursor): void
    {
        $state   = new State($code);
        $scanner = new Scanner($state);

        do {
            $status = $scanner->scanForToken();
            $state->setActiveToken($scanner->getToken()->opcode);
        } while ($status === ScannerStatus::OK);

        $this->assertSame([ScannerStatus::ERR, $cursor], [$status, $state->getCursor()]);
    }

    public function testSetAfterDotAndWhitespaceIsKeyword(): void
    {
        $this->assertSame(
            [['IDENTIFIER', 'a'], ['DOT', null], ['SET', null]],
            $this->codeTokens('{{ a. set }}')
        );
    }

    public function testSetAndIsDirectlyAfterDotAreIdentifiers(): void
    {
        $this->assertSame(
            [['IDENTIFIER', 'a'], ['DOT', null], ['IDENTIFIER', 'set'], ['DOT', null], ['IDENTIFIER', 'IS']],
            $this->codeTokens('{{ a.set.IS }}')
        );
    }

    public function testStatementPositionCountsStatementKeywords(): void
    {
        $state   = new State('{{ a }}{% if b is not c and not d is defined %}');
        $scanner = new Scanner($state);

        while ($scanner->scanForToken() === ScannerStatus::OK) {
            $state->setActiveToken($scanner->getToken()->opcode);
        }

        // {{, if, is not, not, is, defined
        $this->assertSame(6, $state->getStatementPosition());
    }

    public function testStringsKeepTheirEscapes(): void
    {
        $this->assertSame(
            [['STRING', 'a\\"b'], ['STRING', "it\\'s"], ['STRING', '']],
            $this->codeTokens('{{ "a\\"b" \'it\\\'s\' "" }}')
        );
    }

    public function testUnclosedVerbatimFlushesAtTheEnd(): void
    {
        $this->assertSame(
            [['VERBATIM', null], ['RAW_FRAGMENT', "x\n"], ['EOF', null]],
            $this->codeTokens("{% verbatim %}x\n", ['OPEN_DELIMITER', 'CLOSE_DELIMITER'], true)
        );
    }

    public function testUnterminatedCommentEndsTheScan(): void
    {
        $this->assertSame([['EOF', null, 2]], $this->scan("a{# b\n"));
    }

    public function testVerbatimCapturesTagsLiterally(): void
    {
        $this->assertSame(
            [
                ['OPEN_DELIMITER', null], ['VERBATIM', null], ['CLOSE_DELIMITER', null],
                ['RAW_FRAGMENT', '{{ a }}{% if %}'],
                ['OPEN_DELIMITER', null], ['ENDVERBATIM', null], ['CLOSE_DELIMITER', null],
            ],
            $this->codeTokens('{% verbatim %}{{ a }}{% if %}{% endverbatim %}', [])
        );
    }

    public function testVerbatimEndTagIgnoresCaseSpacingAndDash(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'x'], ['ENDVERBATIM', null]],
            $this->codeTokens(
                "{% verbatim %}x{%-  \n ENDVERBATIM %}",
                ['OPEN_DELIMITER', 'CLOSE_DELIMITER', 'VERBATIM']
            )
        );
    }

    public function testVerbatimEndTagMustBeAWholeWord(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'a{% endverbatimx %}b'], ['ENDVERBATIM', null]],
            $this->codeTokens(
                '{% verbatim %}a{% endverbatimx %}b{% endverbatim %}',
                ['OPEN_DELIMITER', 'CLOSE_DELIMITER', 'VERBATIM']
            )
        );
    }

    public function testWhitespaceControlKeepsFormFeed(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', "a\x0C"], ['IDENTIFIER', 'b']],
            $this->codeTokens("a\x0C {{- b }}")
        );
    }

    public function testWhitespaceControlTrimsBothSides(): void
    {
        $this->assertSame(
            [['RAW_FRAGMENT', 'a'], ['IDENTIFIER', 'b'], ['RAW_FRAGMENT', 'c']],
            $this->codeTokens("a \t\n {{- b -}} \x0B\r c")
        );
    }

    /**
     * Tokens without lines, IGNORE and the given opcodes (delimiters by
     * default), and without the final status unless asked for.
     *
     * @param list<string> $skip
     *
     * @return list<array{0: string, 1: ?string}>
     */
    private function codeTokens(string $code, array $skip = self::DELIMITERS, bool $withStatus = false): array
    {
        $tokens = [];

        foreach ($this->tokenize($code) as [$name, $value]) {
            $isStatus = $name === 'EOF' || $name === 'ERR';
            if ($name === 'IGNORE' || in_array($name, $skip, true) || ($isStatus && !$withStatus)) {
                continue;
            }
            $tokens[] = [$name, $value];
        }

        return $tokens;
    }

    /**
     * @param list<string> $only
     *
     * @return list<array{0: string, 1: ?string, 2: int}>
     */
    private function lineTokens(string $code, array $only): array
    {
        $tokens = [];

        foreach ($this->tokenize($code) as [$name, $value, $line]) {
            if (in_array($name, $only, true)) {
                $tokens[] = [$name, $value, $line];
            }
        }

        return $tokens;
    }

    /**
     * Every token including the final status, as [name, value, line after].
     *
     * @return list<array{0: string, 1: ?string, 2: int}>
     */
    private function scan(string $code): array
    {
        $tokens = [];

        foreach ($this->tokenize($code) as [$name, $value, $line]) {
            if ($name !== 'IGNORE') {
                $tokens[] = [$name, $value, $line];
            }
        }

        return $tokens;
    }

    /**
     * Drives the scanner the way the parser driver does: the active token is
     * updated after every token, IGNORE included (base.c line 303).
     *
     * @return list<array{0: string, 1: ?string, 2: int}>
     */
    private function tokenize(string $code): array
    {
        $state   = new State($code);
        $scanner = new Scanner($state);
        $tokens  = [];

        while (($status = $scanner->scanForToken()) === ScannerStatus::OK) {
            $token = $scanner->getToken();
            $state->setActiveToken($token->opcode);
            $tokens[] = [Opcode::from($token->opcode)->name, $token->value, $state->getActiveLine()];
        }

        $tokens[] = [$status->name, null, $state->getActiveLine()];

        return $tokens;
    }
}
