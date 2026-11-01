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
 * Scanner rules from scanner.re that are easy to get subtly wrong: longest
 * match vs keyword priority, case-insensitive keywords, the `is not` variants,
 * context-sensitive `set` / `is` / `default`, two-token `**` and `//`,
 * number forms, string escapes, comments, NUL bytes and whitespace control.
 */

return [
    // Empty and blank input
    'empty'                         => '',
    'single space'                  => ' ',
    'single newline'                => "\n",
    'comment only'                  => '{# #}',
    'empty statement'               => '{% %}',
    'empty statements'              => '{% %}{% %}',
    'raw only'                      => 'hello',

    // NUL bytes end the scan
    'nul in raw'                    => "x\0y",
    'nul between echoes'            => "{{ a }}\0{{ b }}",
    'nul in code'                   => "{{ a \0 b }}",
    'nul in string'                 => "{{ 'a\0b' }}",

    // Comments
    'comment: empty braces'         => '{#}',
    'comment: empty braces raw'     => '{#}x',
    'comment: zero'                 => '{# 0 #}x',
    'comment: zero tight'           => '{#0#}x',
    'comment: joins raw'            => 'a{# c #}b',
    'comment: joins raw spaced'     => 'Hello {# c #} World',
    'comment: unterminated'         => 'a{#',
    'comment: unterminated text'    => 'a{# b',
    'comment: contains tags'        => '{# {{ a }} {% if %} #}x',
    'comment: hash brace inside'    => '{# a # } #}x',
    'comment: before statement'     => '{# a #}{% if b %}c{% endif %}',
    'comment: dash'                 => 'x {#- c -#} y',

    // is not / not equals
    'is not: one space'             => '{{ a is not b }}',
    'is not: upper'                 => '{{ a IS NOT b }}',
    'is not: mixed'                 => '{{ a Is Not b }}',
    'is not: two spaces'            => '{{ a is  not b }}',
    'is not: two spaces upper'      => '{{ a IS  NOT b }}',
    'is not: tab'                   => "{{ a is\tnot b }}",
    'is not: nothing'               => '{{ a is nothing }}',
    'is not: underscore'            => '{{ a is not_x }}',
    'is not: defined'               => '{{ a is not defined }}',
    'is: identifier'                => '{{ a is b }}',
    'not equals: bang'              => '{{ a != b }}',
    'not equals: diamond'           => '{{ a <> b }}',
    'not equals: defined'           => '{{ a != defined }}',
    'not equals: empty'             => '{{ a <> empty }}',

    // Context-sensitive identifiers
    'dot: set'                      => '{{ a.set }}',
    'dot: is'                       => '{{ a.is }}',
    'dot: spaced set'               => '{{ a . set }}',
    'dot: if'                       => '{{ a.if }}',
    'dot: default'                  => '{{ a.default }}',
    'default: echo'                 => '{{ default }}',
    'default: filter'               => '{{ a|default("x") }}',
    'default: upper filter'         => '{{ a|DEFAULT("x") }}',
    'default: in switch'            => '{% switch a %}{% default %}x{% endswitch %}',
    'default: upper in switch'      => '{% switch a %}{% DEFAULT %}x{% endswitch %}',
    'default: echo in switch'       => '{% switch a %}{% case 1 %}{{ default }}{% endswitch %}',
    'default: filter in switch'     => '{% switch a %}{% case 1 %}{{ a|default(1) }}{% endswitch %}',
    'cache: identifier'             => '{{ cachex }}',

    // Two-token operators
    'pow: tight'                    => '{{ a ** b }}',
    'pow: spaced'                   => '{{ a * * b }}',
    'floor div: tight'              => '{{ a // b }}',
    'floor div: spaced'             => '{{ a / / b }}',
    'incr'                          => '{{ a++ }}',
    'decr'                          => '{{ a-- }}',
    'minus minus spaced'            => '{{ a - - b }}',

    // Numbers
    'number: double'                => '{{ 1.5 }}',
    'number: trailing dot'          => '{{ 1. }}',
    'number: leading dot'           => '{{ .5 }}',
    'number: range tight'           => '{{ 1..2 }}',
    'number: range spaced'          => '{{ 1 .. 2 }}',
    'number: leading zeros'         => '{{ 007 }}',
    'number: double double'         => '{{ 1.5.6 }}',
    'number: big'                   => '{{ 99999999999999999999 }}',
    'number: digit identifier'      => '{{ 1a }}',

    // Identifiers
    'ident: namespaced'             => '{{ \\Foo\\Bar }}',
    'ident: namespaced call'        => '{{ Foo\\Bar() }}',
    'ident: underscore'             => '{{ _a }}',
    'ident: digits'                 => '{{ a1 }}',
    'ident: keyword prefix'         => '{{ iffy }}',
    'ident: keyword suffix'         => '{{ endiff }}',
    'ident: nullable'               => '{{ nullable }}',
    'ident: true prefix'            => '{{ trueish }}',

    // Keyword case
    'case: if upper'                => '{% IF a %}x{% ENDIF %}',
    'case: if mixed'                => '{% If a %}x{% EndIf %}',
    'case: true upper'              => '{{ TRUE }}',
    'case: null mixed'              => '{{ Null }}',
    'case: and upper'               => '{{ a AND b }}',
    'case: or mixed'                => '{{ a Or b }}',
    'case: not upper'               => '{{ NOT a }}',
    'case: for upper'               => '{% FOR a IN b %}x{% ENDFOR %}',
    'case: set upper'               => '{% SET a = 1 %}',
    'case: block upper'             => '{% BLOCK a %}x{% ENDBLOCK %}',
    'case: is defined upper'        => '{{ a IS DEFINED }}',

    // Strings
    'string: escaped dq'            => '{{ "a\\"b" }}',
    'string: escaped sq'            => "{{ 'a\\'b' }}",
    'string: real newline'          => "{{ \"a\nb\" }}",
    'string: escape n'              => '{{ "\\n" }}',
    'string: empty sq'              => "{{ '' }}",
    'string: empty dq'              => '{{ "" }}',
    'string: contains open'         => '{{ "a{{b" }}',
    'string: contains close'        => '{{ "%}" }}',
    'string: contains close echo'   => '{{ "}}" }}',
    'string: backslash backslash'   => '{{ "a\\\\" }}',
    'string: dollar'                => '{{ "$a" }}',
    'string: mixed quotes'          => "{{ \"it's\" ~ 'say \"hi\"' }}",

    // Delimiters and whitespace inside code
    'tight echo'                    => '{{a}}',
    'tight statement'               => '{%if a%}x{%endif%}',
    'tabs in code'                  => "{{ a\t~\tb }}",
    'cr in code'                    => "{{\ra\r}}",
    'echo then text'                => '{{ a }}text',
    'text then echo'                => 'text{{ a }}',
    'adjacent echoes'               => '{{ a }}{{ b }}',
    'brace in raw'                  => 'a { b } c',
    'percent in raw'                => 'a % b %} c',
    'lone open brace end'           => 'a {',
    'lone open brace percent end'   => 'a {%',

    // Verbatim
    'verbatim: echo inside'         => '{% verbatim %}{{ a }}{% endverbatim %}',
    'verbatim: empty'               => '{% verbatim %}{% endverbatim %}',
    'verbatim: upper'               => '{% VERBATIM %}x{% ENDVERBATIM %}',
    'verbatim: statement inside'    => '{% verbatim %}{% if %}{% endverbatim %}',
    'verbatim: spaced end'          => '{% verbatim %}x{%   endverbatim   %}',
    'verbatim: tight end'           => '{% verbatim %}x{%endverbatim%}',
    'verbatim: then echo'           => '{% verbatim %}x{% endverbatim %}{{ a }}',
    'verbatim: twice'               => '{% verbatim %}a{% endverbatim %}b{% verbatim %}c{% endverbatim %}',
    'verbatim: whitespace control'  => '{%- verbatim -%} x {%- endverbatim -%}',
    'verbatim: in if'               => '{% if a %}{% verbatim %}{{ b }}{% endverbatim %}{% endif %}',
    'verbatim: endverbatim text'    => '{% verbatim %}a {% endverbatim b %}c{% endverbatim %}',
    'verbatim: identifier'          => '{{ verbatimx }}',
];
