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
 * Regenerates the golden files for every corpus group in
 * `tests/fixtures/parity` from the cphalcon C implementation.
 *
 *     php -d extension=phalcon tests/generate-golden.php
 *
 * Unlike a snapshot, the goldens are a cross-implementation reference: they
 * record what ext-phalcon does, never what this package does. The script
 * therefore refuses to run without the extension instead of falling back to
 * the pure-PHP parser. Each golden records the extension version that
 * produced it.
 *
 * Per case it stores the parse outcome (`Compiler::parse()`) and the compile
 * outcome (`Compiler::compileString()`), each as {outcome, value, exception?}.
 * Each case is written as one compact JSON line, so the files stay small even
 * for deeply nested ASTs and a diff shows exactly which cases changed.
 *
 * PHP notices raised by cphalcon's compiler (it reads optional AST keys
 * unguarded) are silenced: they are not part of either outcome.
 */

require __DIR__ . '/../vendor/autoload.php';

use Phalcon\Mvc\View\Engine\Volt\Compiler as CCompiler;
use Phalcon\Tests\Support\Parity;

if (!extension_loaded('phalcon') || !class_exists(CCompiler::class)) {
    fwrite(STDERR, "ext-phalcon is not loaded; refusing to generate goldens from anything else.\n");
    fwrite(STDERR, "Run: php -d extension=phalcon tests/generate-golden.php\n");

    exit(1);
}

$generator = 'ext-phalcon ' . phpversion('phalcon');
$written   = 0;
$cases     = 0;

echo "Generating from {$generator} on PHP " . PHP_VERSION . "\n";

set_error_handler(static fn (): bool => true, E_NOTICE | E_WARNING | E_DEPRECATED);

$encode = static fn (mixed $value): string => json_encode(
    $value,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    Parity::JSON_DEPTH
);

foreach (Parity::corpus() as $group => $sources) {
    $lines = [];

    foreach ($sources as $label => $source) {
        $case = [
            'source'  => $source,
            'parse'   => Parity::run(static fn (string $code): mixed => (new CCompiler())->parse($code), $source),
            'compile' => Parity::run(static fn (string $code): mixed => (new CCompiler())->compileString($code), $source),
        ];

        $lines[] = '        ' . $encode((string) $label) . ': ' . $encode($case);
        $cases++;
    }

    $json = "{\n"
        . '    "generator": ' . $encode($generator) . ",\n"
        . "    \"cases\": {\n"
        . implode(",\n", $lines) . "\n"
        . "    }\n"
        . "}\n";

    file_put_contents(Parity::fixturesPath() . '/' . $group . '.json', $json);
    $written++;

    echo sprintf("  %-20s %4d case(s)\n", $group, count($sources));
}

echo "Wrote {$written} golden file(s), {$cases} case(s).\n";
