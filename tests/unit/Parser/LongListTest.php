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
use function implode;
use function microtime;
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
     * cphalcon tests/unit/Mvc/View/Engine/Volt/Compiler/CompileStringTest.php
     */
    public function testLargeArrayLiteralCompilesQuickly(): void
    {
        $count    = 40000;
        $template = '{{ [' . implode(',', array_fill(0, $count, '1')) . '] }}';

        $start  = microtime(true);
        $actual = (new Compiler())->compileString($template);
        $spent  = microtime(true) - $start;

        $this->assertSame($count - 1, substr_count($actual, ', 1'));
        $this->assertLessThan(3.0, $spent);
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
}
