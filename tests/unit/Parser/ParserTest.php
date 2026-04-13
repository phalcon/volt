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

namespace Phalcon\Tests\Unit\Parser;

use Phalcon\Volt\Exception;
use Phalcon\Volt\Parser\Parser;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testEmptyTemplateReturnsEmptyArray(): void
    {
        $parser = new Parser();

        $this->assertSame([], $parser->parse('', 'test.volt'));
    }

    public function testRawTextFragment(): void
    {
        $parser = new Parser();
        $result = $parser->parse('Hello World', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(357, $result[0]['type']); // PHVOLT_T_RAW_FRAGMENT
        $this->assertSame('Hello World', $result[0]['value']);
    }

    public function testEchoVariable(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{{ name }}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(359, $result[0]['type']); // PHVOLT_T_ECHO
        $this->assertSame('name', $result[0]['expr']['value']);
    }

    public function testIfStatement(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% if active %}yes{% endif %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(300, $result[0]['type']); // PHVOLT_T_IF
    }

    public function testForLoop(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% for item in items %}{{ item }}{% endfor %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(304, $result[0]['type']); // PHVOLT_T_FOR
        $this->assertSame('item', $result[0]['variable']);
        $this->assertSame('items', $result[0]['expr']['value']);
    }

    public function testSetStatement(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% set x = 1 %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testExtendsStatement(): void
    {
        $parser = new Parser();
        $result = $parser->parse(
            "{% extends 'base.volt' %}{% block content %}hello{% endblock %}",
            'child.volt'
        );

        $this->assertIsArray($result);
    }

    public function testSyntaxErrorThrowsException(): void
    {
        $this->expectException(Exception::class);

        $parser = new Parser();
        $parser->parse('{% endif %}', 'test.volt');
    }

    public function testTemplatePathInErrorMessage(): void
    {
        try {
            $parser = new Parser();
            $parser->parse('{% endif %}', 'mytemplate.volt');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('mytemplate.volt', $e->getMessage());
        }
    }

    public function testNestedSwitchThrowsException(): void
    {
        $this->expectException(Exception::class);

        $parser = new Parser();
        $parser->parse('{% switch x %}{% switch y %}{% endswitch %}{% endswitch %}', 'test.volt');
    }

    public function testDebugMode(): void
    {
        $debugFile = '/app/volt_debug_test.txt';
        $parser    = new Parser();
        $parser->setDebug(true)->setDebugFile($debugFile);

        $result = $parser->parse('{{ name }}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertFileExists($debugFile);

        @unlink($debugFile);
    }

    public function testElseForViaElse(): void
    {
        $parser = new Parser();
        $result = $parser->parse(
            '{% for x in items %}{{ x }}{% else %}empty{% endfor %}',
            'test.volt'
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(304, $result[0]['type']); // PHVOLT_T_FOR
    }

    public function testElsefor(): void
    {
        $parser = new Parser();
        $result = $parser->parse(
            '{% for x in items %}{{ x }}{% elsefor %}empty{% endfor %}',
            'test.volt'
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(304, $result[0]['type']); // PHVOLT_T_FOR
    }

    public function testElseifWithoutIfThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected ENDIF');

        $parser = new Parser();
        $parser->parse('{% elseif x %}', 'test.volt');
    }

    public function testSwitchWithCase(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% switch x %}{% case 1 %}one{% endswitch %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(411, $result[0]['type']); // PHVOLT_T_SWITCH
    }

    public function testSwitchWithDefault(): void
    {
        $parser = new Parser();
        $result = $parser->parse(
            '{% switch x %}{% default %}fallback{% endswitch %}',
            'test.volt'
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(411, $result[0]['type']); // PHVOLT_T_SWITCH
    }

    public function testCaseWithoutSwitchThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected CASE');

        $parser = new Parser();
        $parser->parse('{% case 1 %}', 'test.volt');
    }

    public function testEndswitchWithoutSwitchThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected ENDSWITCH');

        $parser = new Parser();
        $parser->parse('{% endswitch %}', 'test.volt');
    }

    public function testBlockInsideBlockThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Embedding blocks into other blocks is not supported');

        $parser = new Parser();
        $parser->parse(
            '{% block outer %}{% block inner %}{% endblock %}{% endblock %}',
            'test.volt'
        );
    }

    public function testMacroInsideMacroThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Embedding macros into other macros is not allowed');

        $parser = new Parser();
        $parser->parse(
            '{% macro outer() %}{% macro inner() %}{% endmacro %}{% endmacro %}',
            'test.volt'
        );
    }

    public function testMacroAndEndmacro(): void
    {
        $parser = new Parser();
        $result = $parser->parse(
            '{% macro greet(name) %}Hello {{ name }}{% endmacro %}',
            'test.volt'
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(322, $result[0]['type']); // PHVOLT_T_MACRO
    }

    public function testCallAndEndcall(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% call greet() %}{% endcall %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(325, $result[0]['type']); // PHVOLT_T_CALL
    }

    public function testRawAndEndraw(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% raw %}{{ not_evaluated }}{% endraw %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(400, $result[0]['type']); // PHVOLT_T_RAW
    }

    public function testIncludeWith(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% include "partial.volt" with vars %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(313, $result[0]['type']); // PHVOLT_T_INCLUDE
    }

    public function testReturnInMacro(): void
    {
        $parser = new Parser();
        $result = $parser->parse(
            '{% macro compute(x) %}{% return x %}{% endmacro %}',
            'test.volt'
        );

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(322, $result[0]['type']); // PHVOLT_T_MACRO
    }

    public function testAddAssign(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% set counter += 1 %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testSubAssign(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% set counter -= 1 %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testMulAssign(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% set counter *= 2 %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testDivAssign(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{% set counter /= 2 %}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testOpenEdelimiterInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser();
        $parser->parse('{% extends "base.volt" %}{{ something }}', 'test.volt');
    }

    public function testForInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser();
        $parser->parse('{% extends "base.volt" %}{% for x in y %}{% endfor %}', 'test.volt');
    }

    public function testSwitchInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser();
        $parser->parse('{% extends "base.volt" %}{% switch x %}{% endswitch %}', 'test.volt');
    }

    public function testSetInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser();
        $parser->parse('{% extends "base.volt" %}{% set x = 1 %}', 'test.volt');
    }

    public function testRawFragmentInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser();
        $parser->parse('{% extends "base.volt" %}non-blank content', 'test.volt');
    }

    public function testScannerErrorNearEof(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Scanning error');

        $parser = new Parser();
        $parser->parse(
            '{{ link_to("album/" ~ album.id ~ "/" ~ $album.uri, "test") }}',
            'test.volt'
        );
    }

    public function testIfInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser();
        $parser->parse('{% extends "base.volt" %}{% if x %}yes{% endif %}', 'test.volt');
    }

    public function testCurlyBracketEmptyExpression(): void
    {
        $parser = new Parser();
        $result = $parser->parse('{{ {} }}', 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(359, $result[0]['type']); // PHVOLT_T_ECHO
    }

    public function testCurlyBracketDictExpression(): void
    {
        $parser = new Parser();
        $result = $parser->parse("{{ {'key': 'value'} }}", 'test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(359, $result[0]['type']); // PHVOLT_T_ECHO
    }

    public function testParserIsReusable(): void
    {
        $parser = new Parser();

        $result1 = $parser->parse('{{ name }}', 'first.volt');
        $result2 = $parser->parse('{% if active %}yes{% endif %}', 'second.volt');

        $this->assertSame(359, $result1[0]['type']); // PHVOLT_T_ECHO
        $this->assertSame(300, $result2[0]['type']); // PHVOLT_T_IF
    }
}
