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
 * Harvests Volt snippets from existing test suites into a parity corpus.
 *
 * Every string literal (single, double or nowdoc/heredoc without
 * interpolation, joined across `.` concatenations) that contains a Volt tag and
 * no compiled PHP is collected, and so is every `*.volt` template under the
 * given directories. Duplicates are dropped; the first occurrence wins, so list
 * the most authoritative source (cphalcon) first. `{% cache %}` statements are
 * skipped: phalcon/volt removed them on purpose, and those cases live in the
 * allow-listed `cache.php` group instead.
 *
 *     php tests/harvest-corpus.php \
 *         cphalcon=/path/to/cphalcon/tests/unit/Mvc/View/Engine/Volt \
 *         cphalcon-views=/path/to/cphalcon/tests/support/assets/views \
 *         phalcon=/path/to/phalcon/tests/unit/Mvc/View/Engine/Volt \
 *         volt=tests/unit
 *
 * The result is written to `tests/fixtures/parity/harvested.php` as a plain
 * `label => source` array. The corpus only lists inputs; the expected output
 * comes from `tests/generate-golden.php`, which runs them through ext-phalcon.
 */

$output = __DIR__ . '/fixtures/parity/harvested.php';

if ($argc < 2) {
    fwrite(STDERR, "usage: php tests/harvest-corpus.php label=path [label=path ...]\n");

    exit(2);
}

/**
 * @return list<string>
 */
function harvestFiles(string $path): array
{
    if (is_file($path)) {
        return [$path];
    }

    $files    = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        /** @var SplFileInfo $file */
        $extension = $file->getExtension();
        if ($extension === 'php' || $extension === 'volt') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

/**
 * Evaluates one constant PHP string literal token. Only tokens the tokenizer
 * classified as constant strings (or complete heredoc/nowdoc runs without
 * interpolation) are ever passed here, so evaluation cannot execute code.
 */
function literalValue(string $literal): string
{
    /** @var string */
    return eval('return ' . $literal . ';');
}

/**
 * Returns every constant string expression in a PHP file, joining literals
 * that are concatenated with `.`.
 *
 * @return list<string>
 */
function stringLiterals(string $code): array
{
    $tokens   = token_get_all($code);
    $count    = count($tokens);
    $strings  = [];
    $current  = null;
    $joinNext = false;

    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];

        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        $literal = null;

        if (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            $literal = literalValue($token[1]);
        } elseif (is_array($token) && $token[0] === T_START_HEREDOC) {
            $source       = $token[1];
            $interpolated = false;
            for ($j = $i + 1; $j < $count; $j++) {
                $inner = $tokens[$j];
                $source .= is_array($inner) ? $inner[1] : $inner;
                if (is_array($inner) && $inner[0] === T_END_HEREDOC) {
                    break;
                }
                if (!is_array($inner) || $inner[0] !== T_ENCAPSED_AND_WHITESPACE) {
                    $interpolated = true;
                }
            }
            $i = $j;
            if (!$interpolated) {
                $literal = literalValue($source);
            }
        }

        if ($literal !== null) {
            if ($joinNext && $current !== null) {
                $current .= $literal;
            } else {
                if ($current !== null) {
                    $strings[] = $current;
                }
                $current = $literal;
            }
            $joinNext = false;

            continue;
        }

        if ($token === '.' && $current !== null) {
            $joinNext = true;

            continue;
        }

        if ($current !== null) {
            $strings[] = $current;
            $current   = null;
        }
        $joinNext = false;
    }

    if ($current !== null) {
        $strings[] = $current;
    }

    return $strings;
}

function isVoltSnippet(string $candidate): bool
{
    return preg_match('/\{[{%#]/', $candidate) === 1
        && !str_contains($candidate, '<?')
        && preg_match('/\{%-?\s*(?:end)?cache\b/i', $candidate) !== 1;
}

$corpus = [];
$seen   = [];

foreach (array_slice($argv, 1) as $argument) {
    [$label, $root] = array_pad(explode('=', $argument, 2), 2, '');

    if ($label === '' || $root === '' || !file_exists($root)) {
        fwrite(STDERR, "skipping invalid source '{$argument}'\n");

        continue;
    }

    $root  = rtrim($root, '/');
    $added = 0;

    foreach (harvestFiles($root) as $file) {
        $relative = ltrim(substr($file, strlen($root)), '/');
        $contents = file_get_contents($file);

        if ($contents === false) {
            continue;
        }

        $candidates = str_ends_with($file, '.volt') ? [$contents] : stringLiterals($contents);
        $index      = 0;

        foreach ($candidates as $candidate) {
            if (!isVoltSnippet($candidate) || isset($seen[$candidate])) {
                continue;
            }

            $seen[$candidate] = true;
            $index++;
            $key          = str_ends_with($file, '.volt')
                ? "{$label}:{$relative}"
                : "{$label}:" . preg_replace('/\.php$/', '', $relative) . "#{$index}";
            $corpus[$key] = $candidate;
            $added++;
        }
    }

    echo "  {$label}: {$added} snippet(s)\n";
}

$export = "<?php\n\n"
    . "/**\n"
    . " * Generated by tests/harvest-corpus.php - do not edit by hand.\n"
    . " * Add hand-written cases to the other files in this directory instead.\n"
    . " */\n\n"
    . "declare(strict_types=1);\n\n"
    . 'return ' . var_export($corpus, true) . ";\n";

file_put_contents($output, $export);

echo 'Wrote ' . count($corpus) . " snippet(s) to tests/fixtures/parity/harvested.php\n";
