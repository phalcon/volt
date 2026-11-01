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

namespace Phalcon\Tests\Unit\Parser;

use Phalcon\Tests\Support\Parity;
use Phalcon\Volt\Parser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function is_array;

/**
 * Replays every case of the parity corpus against the golden outcome recorded
 * from ext-phalcon by `tests/generate-golden.php`.
 *
 * `assertSame()` rather than `assertEquals()`: key order and int/string types
 * are part of the AST format, and only an identity comparison notices drift.
 * Exceptions compare by message only - cphalcon throws
 * Phalcon\Mvc\View\Exception, this package Phalcon\Volt\Exception.
 *
 * Cases in `allow-list.php` are deliberate phalcon/volt divergences (e.g. the
 * removed `{% cache %}` statement) and are checked against their recorded
 * phalcon/volt expectation instead of the C golden.
 */
final class FixturesTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: array{outcome: mixed, value: mixed}}>
     */
    public static function goldenProvider(): array
    {
        $allowList = Parity::allowList();
        $cases     = [];

        foreach (Parity::corpus() as $group => $sources) {
            $golden = Parity::golden($group);
            /** @var array<string, array<string, mixed>> $goldenCases */
            $goldenCases = is_array($golden['cases'] ?? null) ? $golden['cases'] : [];

            foreach ($sources as $label => $source) {
                $key = $group . '/' . $label;
                if (isset($allowList[$key])) {
                    $expected = Parity::comparable($allowList[$key]);
                } else {
                    /** @var array<string, mixed> $parse */
                    $parse    = $goldenCases[$label]['parse'] ?? ['outcome' => 'missing golden', 'value' => null];
                    $expected = Parity::comparable($parse);
                }

                $cases[$key] = [$source, $expected];
            }
        }

        return $cases;
    }

    /**
     * @param array{outcome: mixed, value: mixed} $expected
     */
    #[DataProvider('goldenProvider')]
    public function testParseMatchesCphalcon(string $source, array $expected): void
    {
        $actual = Parity::comparable(
            Parity::run(static fn (string $code): array => (new Parser())->parse($code), $source)
        );

        $this->assertSame($expected, $actual);
    }
}
