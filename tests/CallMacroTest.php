<?php

declare(strict_types=1);

namespace Tests;

use Phalcon\Volt\Compiler;
use PHPUnit\Framework\TestCase;

final class CallMacroTest extends TestCase
{
    /**
     * Tests Phalcon\Mvc\View\Engine\Volt :: callMacro() - PHP function
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-12-28
     * @issue  https://github.com/phalcon/cphalcon/issues/15842
     */
    public function testMvcViewEngineVoltCallMacroPhpFunction(): void
    {
        $compiler = new Compiler();

        $source   = '{{ str_replace("a", "b", "aabb") }}';
        $expected = "<?= \$this->callMacro('str_replace', ['a', 'b', 'aabb']) ?>";
        $actual   = $compiler->compileString($source);

        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Mvc\View\Engine\Volt :: callMacro() - PHP function
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-12-28
     * @issue  https://github.com/phalcon/cphalcon/issues/15842
     */
    public function testMvcViewEngineVoltCallMacroPhpFunctionDoesNotExist(): void
    {
        $compiler = new Compiler();

        $source   = '{{ myfunction("a") }}';
        $expected = "<?= \$this->callMacro('myfunction', ['a']) ?>";
        $actual   = $compiler->compileString($source);

        $this->assertSame($expected, $actual);
    }
}
