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

final class AddFunctionTest extends TestCase
{
    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: addFunction()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2017-01-17
     *
     * @dataProvider getVoltAddFunction
     */
    public function testMvcViewEngineVoltCompilerAddFunction(
        string $name,
        string $funcName,
        string $voltName,
        string $expected
    ): void {
        $compiler = new Compiler();

        $compiler->addFunction($name, $funcName);

        $actual = $compiler->compileString($voltName);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: addFunction()
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2017-01-17
     *
     * @dataProvider getVoltAddFunctionClosure
     */
    public function testMvcViewEngineVoltCompilerAddFunctionClosure(
        string $name,
        string $funcName,
        string $voltName,
        string $expected
    ): void {
        $compiler = new Compiler();

        $compiler->addFunction(
            $name,
            function ($arguments) use ($funcName) {
                return $funcName . '(' . $arguments . ')';
            }
        );

        $actual = $compiler->compileString($voltName);
        $this->assertSame($expected, $actual);
    }

    public static function getVoltAddFunction(): array
    {
        return [
            [
                'random',
                'mt_rand',
                '{{ random() }}',
                '<?= mt_rand() ?>',
            ],

            [
                'strtotime',
                'strtotime',
                '{{ strtotime("now") }}',
                '<?= strtotime(\'now\') ?>',
            ],
        ];
    }

    public static function getVoltAddFunctionClosure(): array
    {
        return [
            [
                'shuffle',
                'str_shuffle',
                '{{ shuffle("hello") }}',
                '<?= str_shuffle(\'hello\') ?>',
            ],
        ];
    }
}
