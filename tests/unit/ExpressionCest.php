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

namespace Phalcon\Tests\Unit;

use Phalcon\Volt\Compiler;
use PHPUnit\Framework\TestCase;

final class ExpressionCest extends TestCase
{
    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: expression()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2022-07-01
     */
    public function testCompilerExpression(): void
    {
        $volt = new Compiler();

        // title("\r\n", "\n\n")
        $source   = [
            [
                'expr' => [
                    'type' => 260,
                    'value' => "\t",
                    'file' => 'eval code',
                    'line' => 1,
                ],
                'file' => 'eval code',
                'line' => 1,
            ],
            [
                'expr' => [
                    'type' => 260,
                    'value' => "\n\n",
                    'file' => 'eval code',
                    'line' => 1,
                ],
                'file' => 'eval code',
                'line' => 1,
            ]
        ];

        $expected = "\"\t\", \"\n\n\"";
        $actual   = $volt->expression($source, true);

        $this->assertEquals($expected, $actual);
    }
}
