<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit;

use Phalcon\Volt\Compiler;
use PHPUnit\Framework\TestCase;

final class CompilerTest extends TestCase
{
    public function testCompileString(): void
    {
        $compiler = new Compiler();

        $source   = '{{ str_replace("a", "b", "aabb") }}';
        $expected = "<?= \$this->callMacro('str_replace', ['a', 'b', 'aabb']) ?>";
        $actual   = $compiler->compileString($source);

        $this->assertSame($expected, $actual);
    }

    public function testNotExist(): void
    {
        $compiler  = new Compiler();

        $source   = '{{ myfunction("a") }}';
        $expected = "<?= \$this->callMacro('myfunction', ['a']) ?>";
        $actual   = $compiler->compileString($source);

        $this->assertSame($expected, $actual);
    }
}
