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

use Phalcon\Volt\Compiler;
use Phalcon\Volt\Parser\Parser;
use PHPUnit\Framework\TestCase;

use function array_fill;
use function count;
use function hrtime;
use function implode;
use function str_repeat;
use function substr_count;

/**
 * Long flat lists must parse in linear time and must not count towards the
 * nesting limit: the grammar's lists are left-recursive, so cphalcon keeps
 * them in one parser stack entry however long they grow.
 */
final class LongListTest extends TestCase
{
    /**
     * cphalcon tests/unit/Mvc/View/Engine/Volt/Compiler/CompileStringTest.php,
     * without its 3 s time limit: that limit is sized for the C parser, while
     * this one runs about 13 times slower when coverage is being collected.
     * testParseTimeGrowsLinearly checks the speed instead.
     */
    public function testLargeArrayLiteralCompiles(): void
    {
        $count = 40000;

        $actual = (new Compiler())->compileString($this->arrayTemplate($count));

        $this->assertSame($count - 1, substr_count($actual, ', 1'));
    }

    public function testManyAssignmentsParse(): void
    {
        $statements = (new Parser())->parse('{% set ' . implode(', ', array_fill(0, 3000, 'a = 1')) . ' %}');

        $this->assertSame(3000, count($statements[0]['assignments']));
    }

    public function testManyStatementsParse(): void
    {
        $statements = (new Parser())->parse(str_repeat('{{ a }}x', 5000));

        $this->assertSame(10000, count($statements));
    }

    /**
     * Eight times the input takes about 8 times as long to parse in linear
     * time and up to 64 times as long in quadratic time: this parser measures
     * 7 to 9, the Lemon-table port it replaced measured 46. Comparing two runs
     * on the same machine cancels out its speed and any coverage overhead,
     * which a fixed time limit cannot do.
     */
    public function testParseTimeGrowsLinearly(): void
    {
        $this->secondsToParse(10);

        $small = $this->secondsToParse(2500);
        $large = $this->secondsToParse(20000);

        $this->assertLessThan(20.0, $large / $small);
    }

    private function arrayTemplate(int $count): string
    {
        return '{{ [' . implode(',', array_fill(0, $count, '1')) . '] }}';
    }

    private function secondsToParse(int $count): float
    {
        $template = $this->arrayTemplate($count);
        $parser   = new Parser();

        $start = hrtime(true);
        $parser->parse($template);

        return (hrtime(true) - $start) / 1e9;
    }
}
