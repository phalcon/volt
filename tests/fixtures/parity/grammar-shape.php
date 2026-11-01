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
 * The exact shape of the Lemon grammar: which bodies may be empty, which list
 * forms exist, and the places where the conflict-resolved LALR automaton
 * rejects input a "friendlier" grammar would accept (notably `{% call %}`,
 * where `(` is both an operator and the statement's own delimiter).
 */

return [
    // Empty / non-empty bodies
    'if: empty'                         => '{% if a %}{% endif %}',
    'if: empty both'                    => '{% if a %}{% else %}{% endif %}',
    'if: empty true non-empty else'     => '{% if a %}{% else %}x{% endif %}',
    'if: non-empty true empty else'     => '{% if a %}x{% else %}{% endif %}',
    'if: elseif chain'                  => '{% if a %}1{% elseif b %}2{% elseif c %}3{% else %}4{% endif %}',
    'if: elseif empty'                  => '{% if a %}{% elseif b %}{% endif %}',
    'for: empty'                        => '{% for x in y %}{% endfor %}',
    'for: else'                         => '{% for v in y %}x{% else %}y{% endfor %}',
    'for: elsefor'                      => '{% for v in y if v %}x{% elsefor %}y{% endfor %}',
    'for: key value if'                 => '{% for k, v in y if k %}x{% endfor %}',
    'for: if inside for else'           => '{% for a in b %}{% if c %}x{% else %}y{% endif %}{% endfor %}',
    'for: else in if inside for'        => '{% if a %}{% for b in c %}x{% else %}y{% endfor %}{% endif %}',
    'switch: empty'                     => '{% switch a %}{% endswitch %}',
    'switch: raw before case'           => '{% switch a %} x {% case 1 %}y{% endswitch %}',
    'switch: cases'                     => '{% switch a %}{% case 1 %}x{% case 2 %}{% default %}z{% endswitch %}',
    'block: empty'                      => '{% block a %}{% endblock %}',
    'block: body'                       => '{% block a %}x{% endblock %}',
    'macro: empty'                      => '{% macro m() %}{% endmacro %}',
    'macro: body'                       => '{% macro m() %}x{% endmacro %}',
    'macro: defaults'                   => '{% macro m(a = 1, b = "x", c = 1.5, d = null, e = true, f = false) %}x{% endmacro %}',
    'macro: mixed params'               => '{% macro m(a, b = 1, c) %}x{% endmacro %}',
    'autoescape: empty'                 => '{% autoescape true %}{% endautoescape %}',
    'autoescape: false'                 => '{% autoescape false %}x{% endautoescape %}',
    'verbatim: empty'                   => '{% verbatim %}{% endverbatim %}',

    // call
    'call: no args empty'               => '{% call f() %}{% endcall %}',
    'call: no args body'                => '{% call f() %}x{% endcall %}',
    'call: args empty'                  => '{% call f(1) %}{% endcall %}',
    'call: args body'                   => '{% call f(1) %}x{% endcall %}',
    'call: named args'                  => '{% call f("a": 1, 2) %}x{% endcall %}',
    'call: method'                      => '{% call a.b(1) %}x{% endcall %}',
    'call: chained'                     => '{% call a(1)(2) %}x{% endcall %}',
    'call: enclosed target'             => '{% call (a)(1) %}x{% endcall %}',
    'call: binary target'               => '{% call a + b(1) %}x{% endcall %}',
    'call: is target'                   => '{% call x is y() %}x{% endcall %}',
    'call: filter target'               => '{% call a|b(1) %}x{% endcall %}',

    // Lists and items
    'array: empty'                      => '{{ [] }}',
    'array: hash empty'                 => '{{ {} }}',
    'array: trailing comma'             => '{{ [1,] }}',
    'array: hash trailing comma'        => '{{ {"a": 1, } }}',
    'array: identifier key'             => '{{ {a: 1} }}',
    'array: named in brackets'          => '{{ ["a": 1] }}',
    'array: mixed items'                => '{{ [1, "a": 2, 3] }}',
    'array: integer key'                => '{{ {1: 2} }}',
    'array: nested'                     => '{{ [[1, 2], {"a": [3]}] }}',
    'call: no args'                     => '{{ f() }}',
    'call: named arg'                   => '{{ f("a": 1) }}',
    'call: identifier named arg'        => '{{ f(a: 1) }}',
    'call: trailing comma'              => '{{ f(1,) }}',

    // Slices and access
    'slice: identifier offsets'         => '{{ a[b:c] }}',
    'slice: dotted offset'              => '{{ a[b.c:d] }}',
    'slice: expression offset'          => '{{ a[1 + 2:3] }}',
    'slice: string offset'              => '{{ a["x":1] }}',
    'slice: spaced'                     => '{{ a[1 : 2] }}',
    'slice: empty'                      => '{{ a[:] }}',
    'access: empty'                     => '{{ a[] }}',

    // Misc expressions
    'not without in'                    => '{{ a not b }}',
    'is test'                           => '{{ a is defined }}',
    'enclosed'                          => '{{ (a) }}',
    'enclosed empty'                    => '{{ () }}',

    // Statements
    'set: multiple'                     => '{% set a = 1, b = 2 %}',
    'set: add assign'                   => '{% set a += 1 %}',
    'set: sub assign'                   => '{% set a -= 1 %}',
    'set: mul assign'                   => '{% set a *= 1 %}',
    'set: div assign'                   => '{% set a /= 1 %}',
    'set: dotted'                       => '{% set a.b = 1 %}',
    'set: dotted access dotted'         => '{% set a.b[0].c = 1 %}',
    'set: trailing comma'               => '{% set a = 1, %}',
    'include: plain'                    => '{% include "a" %}',
    'include: with'                     => '{% include "a" with b %}',
    'include: expression'               => '{% include a ~ ".volt" with {"b": c} %}',
    'extends: expression'               => '{% extends a ~ "b" %}',
    'do'                                => '{% do a %}',
    'return'                            => '{% return a %}',
    'break outside for'                 => '{% break %}',
    'continue outside for'              => '{% continue %}',
    'elsefor outside for'               => '{% elsefor %}',
];
