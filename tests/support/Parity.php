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

namespace Phalcon\Tests\Support;

use Throwable;

use function basename;
use function file_get_contents;
use function get_class;
use function glob;
use function in_array;
use function is_array;
use function is_file;
use function json_decode;
use function ksort;
use function sort;

use const JSON_THROW_ON_ERROR;

/**
 * Shared plumbing for the cphalcon parity checks.
 *
 * The corpus lives in `tests/fixtures/parity/<group>.php` (plain
 * `label => volt source` arrays). `tests/generate-golden.php` runs every case
 * through ext-phalcon and records the result in `<group>.json`; FixturesTest
 * replays the goldens against this package and `tests/differential.php`
 * compares both implementations live.
 *
 * Every outcome is normalised to one comparable shape, so "returned an AST"
 * and "threw with this message" compare with a single `===`.
 *
 * @phpstan-type Outcome array{outcome: 'value'|'throw', value: mixed, exception?: class-string}
 */
final class Parity
{
    public const JSON_DEPTH = 4096;

    /**
     * Corpus files that are not case groups.
     */
    private const NON_GROUPS = ['allow-list'];

    /**
     * Expected phalcon/volt outcome for cases where phalcon/volt deliberately
     * differs from cphalcon, keyed by "group/label".
     *
     * @return array<string, array{outcome: 'throw'|'value', value: mixed, reason: string}>
     */
    public static function allowList(): array
    {
        /** @var array<string, array{outcome: 'throw'|'value', value: mixed, reason: string}> */
        return require self::fixturesPath() . '/allow-list.php';
    }

    /**
     * Strips what is allowed to differ between implementations (the exception
     * class: cphalcon throws Phalcon\Mvc\View\Exception, this package throws
     * Phalcon\Volt\Exception) so the rest compares with `===`.
     *
     * @param array<string, mixed> $outcome
     *
     * @return array{outcome: mixed, value: mixed}
     */
    public static function comparable(array $outcome): array
    {
        return ['outcome' => $outcome['outcome'] ?? null, 'value' => $outcome['value'] ?? null];
    }

    /**
     * @return array<string, array<string, string>> group => label => source
     */
    public static function corpus(): array
    {
        $groups = [];
        $files  = glob(self::fixturesPath() . '/*.php') ?: [];
        sort($files);

        foreach ($files as $file) {
            $group = basename($file, '.php');
            if (in_array($group, self::NON_GROUPS, true)) {
                continue;
            }

            /** @var array<string, string> $cases */
            $cases          = require $file;
            $groups[$group] = $cases;
        }

        ksort($groups);

        return $groups;
    }

    public static function fixturesPath(): string
    {
        return __DIR__ . '/../fixtures/parity';
    }

    /**
     * @return array<string, mixed> decoded golden file for one group
     */
    public static function golden(string $group): array
    {
        $path = self::fixturesPath() . '/' . $group . '.json';
        $json = is_file($path) ? file_get_contents($path) : false;
        if ($json === false) {
            return [];
        }

        $decoded = json_decode($json, true, self::JSON_DEPTH, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Runs a parser/compiler and normalises either outcome into one shape.
     *
     * @param callable(string): mixed $run
     *
     * @phpstan-return Outcome
     */
    public static function run(callable $run, string $source): array
    {
        try {
            return ['outcome' => 'value', 'value' => $run($source)];
        } catch (Throwable $ex) {
            return [
                'outcome'   => 'throw',
                'value'     => $ex->getMessage(),
                'exception' => get_class($ex),
            ];
        }
    }
}
