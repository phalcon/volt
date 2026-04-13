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
        $parser = new Parser('');

        $this->assertSame([], $parser->parseView('test.volt'));
    }

    public function testRawTextFragment(): void
    {
        $parser = new Parser('Hello World');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(357, $result[0]['type']); // PHVOLT_T_RAW_FRAGMENT
        $this->assertSame('Hello World', $result[0]['value']);
    }

    public function testEchoVariable(): void
    {
        $parser = new Parser('{{ name }}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(359, $result[0]['type']); // PHVOLT_T_ECHO
        $this->assertSame('name', $result[0]['expr']['value']);
    }

    public function testIfStatement(): void
    {
        $parser = new Parser('{% if active %}yes{% endif %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(300, $result[0]['type']); // PHVOLT_T_IF
    }

    public function testForLoop(): void
    {
        $parser = new Parser('{% for item in items %}{{ item }}{% endfor %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(304, $result[0]['type']); // PHVOLT_T_FOR
        $this->assertSame('item', $result[0]['variable']);
        $this->assertSame('items', $result[0]['expr']['value']);
    }

    public function testSetStatement(): void
    {
        $parser = new Parser('{% set x = 1 %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testExtendsStatement(): void
    {
        $parser = new Parser("{% extends 'base.volt' %}{% block content %}hello{% endblock %}");
        $result = $parser->parseView('child.volt');

        $this->assertIsArray($result);
    }

    public function testSyntaxErrorThrowsException(): void
    {
        $this->expectException(Exception::class);

        $parser = new Parser('{% endif %}');
        $parser->parseView('test.volt');
    }

    public function testTemplatePathInErrorMessage(): void
    {
        try {
            $parser = new Parser('{% endif %}');
            $parser->parseView('mytemplate.volt');
            $this->fail('Expected exception not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('mytemplate.volt', $e->getMessage());
        }
    }

    public function testNestedSwitchThrowsException(): void
    {
        $this->expectException(Exception::class);

        $parser = new Parser('{% switch x %}{% switch y %}{% endswitch %}{% endswitch %}');
        $parser->parseView('test.volt');
    }

    public function testDebugMode(): void
    {
        $debugFile = sys_get_temp_dir() . '/volt_debug_test.txt';
        $parser    = new Parser('{{ name }}');

        $ref = new \ReflectionClass($parser);

        $debugProp = $ref->getProperty('debug');
        $debugProp->setAccessible(true);
        $debugProp->setValue($parser, true);

        $fileProp = $ref->getProperty('debugFile');
        $fileProp->setAccessible(true);
        $fileProp->setValue($parser, $debugFile);

        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertFileExists($debugFile);

        @unlink($debugFile);
    }

    public function testElseForViaElse(): void
    {
        $parser = new Parser('{% for x in items %}{{ x }}{% else %}empty{% endfor %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(304, $result[0]['type']); // PHVOLT_T_FOR
    }

    public function testElsefor(): void
    {
        $parser = new Parser('{% for x in items %}{{ x }}{% elsefor %}empty{% endfor %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(304, $result[0]['type']); // PHVOLT_T_FOR
    }

    public function testElseifWithoutIfThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected ENDIF');

        $parser = new Parser('{% elseif x %}');
        $parser->parseView('test.volt');
    }

    public function testSwitchWithCase(): void
    {
        $parser = new Parser('{% switch x %}{% case 1 %}one{% endswitch %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(411, $result[0]['type']); // PHVOLT_T_SWITCH
    }

    public function testSwitchWithDefault(): void
    {
        $parser = new Parser('{% switch x %}{% default %}fallback{% endswitch %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(411, $result[0]['type']); // PHVOLT_T_SWITCH
    }

    public function testCaseWithoutSwitchThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected CASE');

        $parser = new Parser('{% case 1 %}');
        $parser->parseView('test.volt');
    }

    public function testEndswitchWithoutSwitchThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unexpected ENDSWITCH');

        $parser = new Parser('{% endswitch %}');
        $parser->parseView('test.volt');
    }

    public function testBlockInsideBlockThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Embedding blocks into other blocks is not supported');

        $parser = new Parser('{% block outer %}{% block inner %}{% endblock %}{% endblock %}');
        $parser->parseView('test.volt');
    }

    public function testMacroInsideMacroThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Embedding macros into other macros is not allowed');

        $parser = new Parser('{% macro outer() %}{% macro inner() %}{% endmacro %}{% endmacro %}');
        $parser->parseView('test.volt');
    }

    public function testMacroAndEndmacro(): void
    {
        $parser = new Parser('{% macro greet(name) %}Hello {{ name }}{% endmacro %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(322, $result[0]['type']); // PHVOLT_T_MACRO
    }

    public function testCallAndEndcall(): void
    {
        $parser = new Parser('{% call greet() %}{% endcall %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(325, $result[0]['type']); // PHVOLT_T_CALL
    }

    public function testRawAndEndraw(): void
    {
        $parser = new Parser('{% raw %}{{ not_evaluated }}{% endraw %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(400, $result[0]['type']); // PHVOLT_T_RAW
    }

    public function testIncludeWith(): void
    {
        $parser = new Parser('{% include "partial.volt" with vars %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(313, $result[0]['type']); // PHVOLT_T_INCLUDE
    }

    public function testReturnInMacro(): void
    {
        $parser = new Parser('{% macro compute(x) %}{% return x %}{% endmacro %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(322, $result[0]['type']); // PHVOLT_T_MACRO
    }

    public function testAddAssign(): void
    {
        $parser = new Parser('{% set counter += 1 %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testSubAssign(): void
    {
        $parser = new Parser('{% set counter -= 1 %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testMulAssign(): void
    {
        $parser = new Parser('{% set counter *= 2 %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testDivAssign(): void
    {
        $parser = new Parser('{% set counter /= 2 %}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(306, $result[0]['type']); // PHVOLT_T_SET
    }

    public function testOpenEdelimiterInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser('{% extends "base.volt" %}{{ something }}');
        $parser->parseView('test.volt');
    }

    public function testForInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser('{% extends "base.volt" %}{% for x in y %}{% endfor %}');
        $parser->parseView('test.volt');
    }

    public function testSwitchInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser('{% extends "base.volt" %}{% switch x %}{% endswitch %}');
        $parser->parseView('test.volt');
    }

    public function testSetInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser('{% extends "base.volt" %}{% set x = 1 %}');
        $parser->parseView('test.volt');
    }

    public function testRawFragmentInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser('{% extends "base.volt" %}non-blank content');
        $parser->parseView('test.volt');
    }

    public function testScannerErrorNearEof(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Scanning error');

        $parser = new Parser(
            '{{ link_to("album/" ~ album.id ~ "/" ~ $album.uri, "test") }}'
        );
        $parser->parseView('test.volt');
    }

    public function testIfInExtendsModeThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Child templates only may contain blocks');

        $parser = new Parser('{% extends "base.volt" %}{% if x %}yes{% endif %}');
        $parser->parseView('test.volt');
    }

    public function testCurlyBracketEmptyExpression(): void
    {
        $parser = new Parser('{{ {} }}');
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(359, $result[0]['type']); // PHVOLT_T_ECHO
    }

    public function testCurlyBracketDictExpression(): void
    {
        $parser = new Parser("{{ {'key': 'value'} }}");
        $result = $parser->parseView('test.volt');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame(359, $result[0]['type']); // PHVOLT_T_ECHO
    }
}
