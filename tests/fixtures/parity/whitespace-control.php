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
 * Whitespace control: `{%-` / `{{-` right-trim the raw text before the tag,
 * `-%}` / `-}}` left-trim the raw text after it. The trimmed set is exactly
 * space, \t, \n, \r and \v (not \0, unlike PHP's trim()).
 */

return [
    'echo: left'                    => "a  {{- b }}  c",
    'echo: right'                   => "a  {{ b -}}  c",
    'echo: both'                    => "a  {{- b -}}  c",
    'statement: both'               => "a \n {%- if x %} y {% endif -%} \n z",
    'statement: set'                => "a \n\t{%- set x = 1 -%}\n\t b",
    'statement: all whitespace'     => " \t\x0B\n\r {{- a -}} \t\x0B\n\r ",
    'statement: form feed kept'     => "a\x0C {{- b -}} \x0Cc",
    'statement: nul kept'           => "a\0 {{- b }}",
    'adjacent tags'                 => "x {{- a -}}{{- b -}} y",
    'trim to nothing'               => "   {{- a -}}   ",
    'trim between tags'             => "{{ a -}}   {{- b }}",
    'trim newline lines'            => "{{ a -}}\n\n\n{{ b }}",
    'comment then trim'             => "{# c #} {{- a }}",
    'comment after trim'            => "{{ a -}} {# c #} b",
    'minus spaced not control'      => "a {{ b - }} c",
    'minus before close'            => "{{ a - -}}b",
    'tight minus close'             => "{{ a-}}b",
    'tight minus open'              => "a{{-b}}",
    'open minus number'             => "a {{-1}}",
    'statement tight minus'         => "a {%-if b-%} c {%-endif-%} d",
    'in for body'                   => "{% for a in b -%}\n  {{ a }}\n{%- endfor %}",
    'in switch'                     => "{% switch a -%}\n {% case 1 -%}\n x \n{%- endswitch %}",
    'in extends child'              => "{% extends 'a' -%}\n\n{%- block b -%} x {%- endblock -%}\n",
];
