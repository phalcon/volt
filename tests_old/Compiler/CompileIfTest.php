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

namespace Phalcon\Tests\Integration\Mvc\View\Engine\Volt\Compiler;

use Phalcon\Volt\Compiler;
use PHPUnit\Framework\TestCase;

final class CompileIfTest extends TestCase
{
    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: compileIf()
     *
     * @author Sid Roberts <https://github.com/SidRoberts>
     * @since  2019-05-22
     */
    public function testMvcViewEngineVoltCompilerCompileIf()
    {
        $compiler = new Compiler();

        $expected = '<?php if ($i == 0) { ?>zero<?php } else { ?>not zero<?php } ?>';
        $actual = $compiler->compileString(
            '{% if i == 0 %}zero{% else %}not zero{% endif %}'
        );
        $this->assertSame($expected, $actual);
    }
}
