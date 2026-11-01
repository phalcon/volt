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
 * Operator precedence and associativity, as resolved by the Lemon grammar
 * (parser.php.lemon lines 22-36). Several groupings are counter-intuitive -
 * `*` binds looser than `+`, `!` binds tighter than `[`, `not in` binds
 * tighter than `in` - which is exactly why each one is pinned here.
 */

$expressions = [
    '1 + 2 * 3',
    '1 * 2 + 3',
    '1 - 2 - 3',
    '1 / 2 / 3',
    '1 % 2 * 3',
    '1 * 2 % 3',
    'a ~ b ~ c',
    'a ~ b + c',
    'a + b ~ c',
    'a + b[0]',
    'a * b[0]',
    'a.b[0]',
    'a.b(c)',
    'a.b.c',
    'a.b.c(d)',
    'a.b(c).d',
    'a[0][1]',
    'a(b)(c)',
    'a(b)[0]',
    '!a[0]',
    '!a.b',
    '!a(x)',
    '!a|f',
    '!a and b',
    'not a or b',
    'not a not in b',
    '-a.b',
    '-a * b',
    '-a + b',
    '-a[0]',
    '-a|f',
    '-a++',
    '+a - -b',
    '-1 + 2',
    'a|f(x)',
    'a|f[0]',
    'a|b|c',
    'a|b(1)|c',
    '"x" ~ a|upper',
    'a not in b + c',
    'a + b not in c',
    'a not in b not in c',
    'a not in b[0]',
    'a not in b(1)',
    'a in b + c',
    'a in b in c',
    'a in b and c',
    'a ? b : c ? d : e',
    'a ? b ? c : d : e',
    'a ? b .. c : d',
    'a ? b : c .. d',
    'a ? b and c : d',
    'x + a ? b : c',
    'a ? b : c + d',
    'a .. b + c',
    'a..b..c',
    'a .. b and c',
    'a is defined and b',
    'a is b + c',
    'a is not b + c',
    'x == a is defined',
    'a.b is defined',
    'a[0] is empty',
    'a|f is defined',
    'a ? b : c is defined',
    'a ~ b == c',
    'a == b == c',
    'a < b > c',
    'a === b !== c',
    'a >= b <= c',
    'a == b and c != d',
    'a or b and c',
    'a and b or c',
    '2**3**2',
    '2 + 3 ** 4',
    '2 ** 3 + 4',
    'a // b',
    'a // b * c',
    'a * b // c',
    'a.b++',
    'a++ + b--',
    'a.-b*c',
    'a.(b)',
    'a.[1]',
    'a."s"',
    'a.1',
    '(a + b) * c',
    '(a)(b)',
    '[1, 2][0]',
    '{"a": 1}["a"]',
    '[1, 2]|length',
    'a[1:2]',
    'a[:2]',
    'a[1:]',
    'a[b:c]',
    'a[b]',
    'a[b + 1]',
    'a[1 + 2]',
    'a[b|f]',
    'a[1:2][0]',
    'a.b[1:2]',
    'f(a + b, c * d)',
    'f(a ? b : c)',
    'f("k": a + b)',
    '[a + b, c ~ d]',
    '{"k": a ? b : c}',
];

$cases = [];

foreach ($expressions as $expression) {
    $cases['echo: ' . $expression] = '{{ ' . $expression . ' }}';
}

$cases['set: a = b + c * d']        = '{% set a = b + c * d %}';
$cases['set: a.b.c[0] = 1']         = '{% set a.b.c[0] = 1 %}';
$cases['set: a[0].b = 1']           = '{% set a[0].b = 1 %}';
$cases['if: a or b and c']          = '{% if a or b and c %}x{% endif %}';
$cases['for: iterable a + b']       = '{% for x in a + b %}x{% endfor %}';
$cases['for: if a not in b']        = '{% for x in y if a not in b %}x{% endfor %}';
$cases['do: a.b(c)|f']              = '{% do a.b(c)|f %}';
$cases['return: -a * b']            = '{% return -a * b %}';
$cases['include: with a ~ b']       = '{% include "x" ~ y with a ~ b %}';
$cases['extends: a ~ b']            = '{% extends a ~ "b" %}';
$cases['switch: a + b']             = '{% switch a + b %}{% case c * d %}x{% endswitch %}';

return $cases;
