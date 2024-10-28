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

namespace Phalcon\Tests\Unit\Compiler;

use Phalcon\Volt\Compiler;
use PHPUnit\Framework\TestCase;

class CompileFileTest extends TestCase
{
    public static function defaultFilterProvider(): array
    {
        return [
            [
                'default',
                "<?= (empty(\$robot->price) ? (10.0) : (\$robot->price)) ?>\n",
            ],

            [
                'default_json_encode',
                "<?= json_encode((empty(\$preparedParams) ? ([]) : (\$preparedParams))) ?>\n",
            ],
        ];
    }

    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: compileFile()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2017-01-17
     */
    public function testMvcViewEngineVoltCompilerCompileFile(): void
    {
        $viewFile    = 'tests/_data/views/layouts/compiler.volt';
        $compileFile = $viewFile . 'compiler.volt.php';

        $expected = '<?php if ($some_eval) { ?>
Clearly, the song is: <?= $this->getContent() ?>.
<?php } ?>';

        $volt = new Compiler();

        $volt->compileFile($viewFile, $compileFile);
        $this->assertEquals($expected, file_get_contents($compileFile));

        unlink($compileFile);
    }

    /**
     * Tests Phalcon\Mvc\View\Engine\Volt\Compiler :: compileFile()
     *
     * @issue https://github.com/phalcon/cphalcon/issues/13242
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     *
     * @dataProvider defaultFilterProvider
     */
    public function testMvcViewEngineVoltCompilerCompileFileDefaultFilter(
        string $view,
        string $expected
    ): void {
        $this->markTestSkipped('Compiler.php must be updated from phalcon/phalcon.');

        $viewFile = sprintf('tests/_data/views/filters/%s.volt', $view);
        $compiledFile = $viewFile . '.php';

        $volt = new Compiler();
        $volt->compileFile($viewFile, $compiledFile);
        $this->assertEquals($expected, file_get_contents($compiledFile));
        //unlink($compiledFile);
    }
}
