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
 * Deliberate phalcon/volt divergences from cphalcon, keyed by "group/label".
 *
 * Each entry is the outcome phalcon/volt must produce instead of the C golden.
 * Keep this list short and give every entry a reason.
 */

$removedCache = 'phalcon/volt removed the {% cache %} statement (commit 23585c1): '
    . '`cache` and `endcache` are plain identifiers';

$statement = [
    'outcome' => 'throw',
    'value'   => 'Syntax error, unexpected token IDENTIFIER(cache) in eval code on line 1',
    'reason'  => $removedCache,
];

$identifier = static fn (string $name): array => [
    'type'  => 265,
    'value' => $name,
    'file'  => 'eval code',
    'line'  => 1,
];

$echo = static fn (array $expr): array => [
    'outcome' => 'value',
    'value'   => [['type' => 359, 'expr' => $expr, 'file' => 'eval code', 'line' => 1]],
    'reason'  => $removedCache,
];

return [
    'cache/cache: statement'            => $statement,
    'cache/cache: statement lifetime'   => $statement,
    'cache/cache: statement identifier' => $statement,
    'cache/cache: string key lifetime'  => $statement,
    'cache/cache: identifier lifetime'  => $statement,
    'cache/cache: string key'           => $statement,
    'cache/cache: echo identifier'      => $echo($identifier('cache')),
    'cache/cache: echo endcache'        => $echo($identifier('endcache')),
    'cache/cache: property'             => $echo([
        'type'  => 46,
        'left'  => $identifier('a'),
        'right' => $identifier('cache'),
        'file'  => 'eval code',
        'line'  => 1,
    ]),
    'cache/cache: function'             => $echo([
        'type'      => 350,
        'name'      => $identifier('cache'),
        'arguments' => [
            ['expr' => ['type' => 258, 'value' => '1', 'file' => 'eval code', 'line' => 1], 'file' => 'eval code', 'line' => 1],
        ],
        'file'      => 'eval code',
        'line'      => 1,
    ]),
];
