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

/**
 * Compares this package against the cphalcon C implementation, live, case by
 * case over the whole parity corpus (`tests/fixtures/parity/*.php`).
 *
 *     php -d extension=phalcon tests/differential.php [--verbose]
 *
 * Two comparisons are made per case:
 *
 *  - parse:   Phalcon\Volt\Parser\Parser::parse() vs the C Compiler::parse().
 *             The AST (or the exception message) must be identical. Any
 *             mismatch outside `allow-list.php` fails the run (exit 1).
 *  - compile: Phalcon\Volt\Compiler::compileString() vs the C compileString().
 *             Reported only: the compiler is not part of the parser migration,
 *             but a parser-caused difference shows up here first.
 *
 * Allow-listed cases (deliberate phalcon/volt divergences) are checked against
 * their recorded expectation instead, so the allow-list cannot hide a
 * regression.
 *
 * Requires ext-phalcon; exits 0 with a notice otherwise so it is harmless in a
 * pipeline that does not always have the extension (CI asserts it is loaded).
 */

require __DIR__ . '/../vendor/autoload.php';

use Phalcon\Mvc\View\Engine\Volt\Compiler as CCompiler;
use Phalcon\Tests\Support\Parity;
use Phalcon\Volt\Compiler as PhpCompiler;
use Phalcon\Volt\Parser\Parser as PhpParser;

if (!extension_loaded('phalcon') || !class_exists(CCompiler::class)) {
    fwrite(STDERR, "ext-phalcon is not loaded; nothing to compare against.\n");

    exit(0);
}

// Both compilers raise PHP notices on some inputs; they are not compared.
set_error_handler(static fn (): bool => true, E_NOTICE | E_WARNING | E_DEPRECATED);

$verbose   = in_array('--verbose', $argv, true);
$allowList = Parity::allowList();

$checked           = 0;
$parseMismatches   = 0;
$compileMismatches = 0;
$allowed           = 0;

$show = static function (string $kind, string $key, string $source, array $c, array $php): void {
    echo "MISMATCH [{$kind}] {$key}\n";
    echo '  input : ' . json_encode($source, JSON_UNESCAPED_SLASHES) . "\n";
    echo '  C     : ' . json_encode($c, JSON_UNESCAPED_SLASHES, Parity::JSON_DEPTH) . "\n";
    echo '  PHP   : ' . json_encode($php, JSON_UNESCAPED_SLASHES, Parity::JSON_DEPTH) . "\n\n";
};

foreach (Parity::corpus() as $group => $sources) {
    foreach ($sources as $label => $source) {
        $checked++;
        $key = $group . '/' . $label;

        $cParse   = Parity::comparable(
            Parity::run(static fn (string $code): mixed => (new CCompiler())->parse($code), $source)
        );
        $phpParse = Parity::comparable(
            Parity::run(static fn (string $code): mixed => (new PhpParser())->parse($code), $source)
        );

        if (isset($allowList[$key])) {
            $allowed++;
            $expected = Parity::comparable($allowList[$key]);
            if ($phpParse !== $expected) {
                $parseMismatches++;
                $show('allow-list', $key, $source, $expected, $phpParse);
            }

            continue;
        }

        if ($cParse !== $phpParse) {
            $parseMismatches++;
            $show('parse', $key, $source, $cParse, $phpParse);
        }

        $cCompile   = Parity::comparable(
            Parity::run(static fn (string $code): mixed => (new CCompiler())->compileString($code), $source)
        );
        $phpCompile = Parity::comparable(
            Parity::run(static fn (string $code): mixed => (new PhpCompiler())->compileString($code), $source)
        );

        if ($cCompile !== $phpCompile) {
            $compileMismatches++;
            if ($verbose) {
                $show('compile', $key, $source, $cCompile, $phpCompile);
            } else {
                echo "MISMATCH [compile] {$key}\n";
            }
        }
    }
}

echo "Checked {$checked} case(s) ({$allowed} allow-listed): "
    . "{$parseMismatches} parse mismatch(es), {$compileMismatches} compile mismatch(es) (reported only).\n";

exit($parseMismatches === 0 ? 0 : 1);
