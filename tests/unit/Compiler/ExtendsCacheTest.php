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

use function dirname;
use function file_exists;
use function file_put_contents;
use function filemtime;
use function touch;
use function unlink;

final class ExtendsCacheTest extends TestCase
{
    /**
     * The extends-mode cache holds a serialized array of blocks. A planted
     * file with a serialized object must not instantiate anything and is
     * treated as a cache miss (the template is compiled again).
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-08-26
     */
    public function testCompileExtendsIgnoresPlantedCache(): void
    {
        $viewFile = dirname(__DIR__, 2) . '/_data/views/layouts/compiler.volt';
        $volt     = new Compiler();

        // First compile creates the extends-mode cache and tells us its path.
        $volt->compile($viewFile, true);
        $cache = $volt->getCompiledTemplatePath();

        try {
            $this->assertFileExists($cache);

            file_put_contents($cache, 'O:8:"stdClass":0:{}');
            touch($cache, filemtime($viewFile) + 10);

            $compilation = $volt->compile($viewFile, true);

            $this->assertIsArray($compilation);
            $this->assertNotEmpty($compilation);
        } finally {
            if (file_exists($cache)) {
                unlink($cache);
            }
        }
    }
}
