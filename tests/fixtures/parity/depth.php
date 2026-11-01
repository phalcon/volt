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
 * Parser stack depth. cphalcon's Lemon parser has a fixed 100-entry stack
 * (VVSTACKDEPTH, parser.c:82) and fails closed with "Parsing failed, the
 * template expression is nested too deeply" when a shift would overflow it.
 *
 * Each construct is probed around its predicted boundary (last accepted N and
 * first failing N, derived from the Lemon tables) with a margin of two on each
 * side, so the goldens pin the exact cut-off even if a prediction is off by one.
 */

/**
 * @param callable(int): string $build
 *
 * @return array<string, string>
 */
$probe = static function (string $name, int $lastAccepted, callable $build): array {
    $cases = [];

    for ($n = $lastAccepted - 2; $n <= $lastAccepted + 3; $n++) {
        $cases[sprintf('%s x%d', $name, $n)] = $build($n);
    }

    return $cases;
};

$cases = array_merge(
    $probe(
        'parentheses',
        96,
        static fn (int $n): string => '{{ ' . str_repeat('(', $n) . '1' . str_repeat(')', $n) . ' }}'
    ),
    $probe(
        'parentheses after raw',
        95,
        static fn (int $n): string => 'x{{ ' . str_repeat('(', $n) . '1' . str_repeat(')', $n) . ' }}'
    ),
    $probe(
        'arrays',
        96,
        static fn (int $n): string => '{{ ' . str_repeat('[', $n) . '1' . str_repeat(']', $n) . ' }}'
    ),
    $probe(
        'calls',
        48,
        static fn (int $n): string => '{{ ' . str_repeat('f(', $n) . '1' . str_repeat(')', $n) . ' }}'
    ),
    $probe(
        'access',
        48,
        static fn (int $n): string => '{{ ' . str_repeat('a[', $n) . '1' . str_repeat(']', $n) . ' }}'
    ),
    $probe(
        'unary minus',
        97,
        static fn (int $n): string => '{{ ' . str_repeat('- ', $n) . '1 }}'
    ),
    $probe(
        'unary not',
        97,
        static fn (int $n): string => '{{ ' . str_repeat('! ', $n) . '1 }}'
    ),
    $probe(
        'not in',
        32,
        static fn (int $n): string => '{{ a' . str_repeat(' not in a', $n) . ' }}'
    ),
    $probe(
        'ternary',
        47,
        static fn (int $n): string => '{{ ' . str_repeat('a ? ', $n) . 'b' . str_repeat(' : c', $n) . ' }}'
    ),
    $probe(
        'if',
        23,
        static fn (int $n): string => str_repeat('{% if a %}', $n) . 'x' . str_repeat('{% endif %}', $n)
    ),
    $probe(
        'if empty innermost',
        24,
        static fn (int $n): string => str_repeat('{% if a %}', $n) . str_repeat('{% endif %}', $n)
    ),
    $probe(
        'if after raw',
        19,
        static fn (int $n): string => str_repeat('x{% if a %}', $n) . 'x' . str_repeat('{% endif %}', $n)
    ),
    $probe(
        'if in else',
        11,
        static fn (int $n): string => str_repeat('{% if a %}x{% else %}', $n) . 'x' . str_repeat('{% endif %}', $n)
    ),
    $probe(
        'for',
        15,
        static fn (int $n): string => str_repeat('{% for x in y %}', $n) . 'x' . str_repeat('{% endfor %}', $n)
    ),
    $probe(
        'for key value',
        11,
        static fn (int $n): string => str_repeat('{% for k, v in y %}', $n) . 'x' . str_repeat('{% endfor %}', $n)
    ),
    $probe(
        'autoescape',
        23,
        static fn (int $n): string => str_repeat('{% autoescape true %}', $n)
            . 'x'
            . str_repeat('{% endautoescape %}', $n)
    ),
    $probe(
        'call',
        13,
        static fn (int $n): string => str_repeat('{% call f(1) %}', $n) . 'x' . str_repeat('{% endcall %}', $n)
    ),
    $probe(
        'set access',
        47,
        static fn (int $n): string => '{% set ' . str_repeat('a[', $n) . '1' . str_repeat(']', $n) . ' = 1 %}'
    ),
);

// A syntax error on the overflowing token wins over the overflow itself.
$cases['ordering: bracket instead of paren at 97'] = '{{ ' . str_repeat('(', 97) . '1]' . str_repeat(')', 96) . ' }}';

// A driver check on a token is raised before that token reaches the parser.
$cases['ordering: case inside 23 ifs'] = str_repeat('{% if a %}', 23) . '{% case x %}' . str_repeat('{% endif %}', 23);

// cphalcon tests/unit/Mvc/View/Engine/Volt/Parser/StackOverflowTest.php
$cases['cphalcon: 300 parentheses'] = '{{ ' . str_repeat('(', 300) . '1' . str_repeat(')', 300) . ' }}';

return $cases;
