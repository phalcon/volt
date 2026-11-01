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
 * Every error-message family produced by base.c and the Lemon
 * `%syntax_error` block: token-name quirks (`!`, `%%`, `NOT EQUALS`,
 * `QUESTION MARK`, `UNKNOWN`, `WHITESPACE`), the token value suffix, the
 * "unexpected EOF" path with its unclosed-block suffixes, driver checks
 * (extends / child templates / nesting) and scanning errors.
 */

return [
    // Token value suffix
    'syntax: identifier value'          => '{{ a b }}',
    'syntax: string value'              => '{{ a "s" }}',
    'syntax: empty string value'        => '{{ a "" }}',
    'syntax: zero string value'         => '{{ a "0" }}',
    'syntax: whitespace string value'   => '{{ a " " }}',
    'syntax: integer value'             => '{{ a 1 }}',
    'syntax: double value'              => '{{ a 1.5 }}',
    'syntax: concat after concat'       => '{{ robot.id ~ ~ robot.name }}',
    'syntax: concat after concat nl'    => "{{ robot.id ~ ~ robot.name }}\n",

    // Token names
    'name: null'                        => '{{ a null }}',
    'name: true'                        => '{{ a true }}',
    'name: false'                       => '{{ a false }}',
    'name: pipe'                        => '{{ a | | b }}',
    'name: bang'                        => '{{ a ! b }}',
    'name: not'                         => '{{ a not b }}',
    'name: mod'                         => '{{ a % % b }}',
    'name: minus'                       => '{{ a - - }} x',
    'name: not equals'                  => '{{ a != != b }}',
    'name: diamond'                     => '{{ a <> <> b }}',
    'name: is not'                      => '{{ a is not is not b }}',
    'name: question'                    => '{{ a ? ? b }}',
    'name: colon'                       => '{{ a : b }}',
    'name: range'                       => '{{ a .. .. b }}',
    'name: dot'                         => '{{ a . . b }}',
    'name: comma'                       => '{{ a , b }}',
    'name: assign'                      => '{{ a = b }}',
    'name: add assign'                  => '{{ a += b }}',
    'name: incr prefix'                 => '{{ ++a }}',
    'name: in'                          => '{{ in }}',
    'name: is'                          => '{{ is }}',
    'name: defined'                     => '{{ defined }}',
    'name: empty keyword'               => '{{ empty }}',
    'name: set keyword'                 => '{{ set }}',
    'name: open edelimiter'             => '{{ {{ a }} }}',
    'name: close delimiter'             => '{{ a %} x',
    'name: raw fragment'                => '{% if a x %}',
    'name: verbatim'                    => '{{ a }}{% if verbatim %}x{% endif %} y',
    'name: endcall'                     => '{% if a %}{% endcall %}{% endif %} y',
    'name: default outside switch'      => '{% default %} x',
    'name: default after expression'    => '{% switch a %}{% case 1 %}{{ default default }}{% endswitch %}',
    'name: raw keyword removed'         => '{% raw %}x{% endraw %}',
    'name: with'                        => '{{ with }}',
    'name: brackets'                    => '{{ ] }} x',

    // Error on the final byte reads as EOF
    'eof: dangling operator'            => '{{ 1 + }}',
    'eof: dangling operator newline'    => "{{ 1 + }}\n",
    'eof: open echo'                    => '{{',
    'eof: open statement'               => '{%',
    'eof: open echo and expr'           => '{{ a',
    'eof: empty echo'                   => '{{ }}',
    'eof: empty echo then raw'          => '{{ }} x',
    'eof: close only'                   => '}}',
    'eof: close statement only'         => '%}',
    'eof: extra close'                  => '{{ a }}}}',

    // Unclosed blocks
    'unclosed: if'                      => '{% if a %}x',
    'unclosed: if if'                   => '{% if a %}x{% if b %}y',
    'unclosed: if if if'                => '{% if a %}{% if b %}{% if c %}z',
    'unclosed: for'                     => '{% for a in b %}x',
    'unclosed: for for'                 => '{% for a in b %}x{% for c in d %}y',
    'unclosed: if for'                  => '{% if a %}x{% for b in c %}y',
    'unclosed: for if'                  => '{% for a in b %}x{% if c %}y',
    'unclosed: switch'                  => '{% switch a %}x',
    'unclosed: switch case'             => '{% switch a %}{% case 1 %}x',
    'unclosed: if for switch'           => '{% if a %}{% for b in c %}{% switch d %}x',
    'unclosed: if with else'            => '{% if a %}x{% else %}y',
    'unclosed: block'                   => '{% block a %}x',
    'unclosed: macro'                   => '{% macro a() %}x',

    // Driver (base.c) checks
    'driver: endif without if'          => '{% endif %}',
    'driver: endfor without for'        => '{% endfor %}',
    'driver: endblock without block'    => '{% endblock %}',
    'driver: else without if'           => '{% else %}',
    'driver: elseif without if'         => '{% elseif a %}',
    'driver: case without switch'       => '{% case 1 %}',
    'driver: endswitch without switch'  => '{% endswitch %}',
    'driver: nested switch'             => '{% switch a %}{% switch b %}{% endswitch %}{% endswitch %}',
    'driver: nested block'              => '{% block a %}{% block b %}{% endblock %}{% endblock %}',
    'driver: block in if'               => '{% if a %}{% block b %}x{% endblock %}{% endif %}',
    'driver: block in for'              => '{% for a in b %}{% block c %}x{% endblock %}{% endfor %}',
    'driver: nested macro'              => '{% macro a() %}{% macro b() %}x{% endmacro %}x{% endmacro %}',
    'driver: extends twice'             => '{% extends "a" %}{% extends "b" %}',
    'driver: extends after raw'         => 'x{% extends "a" %}',
    'driver: extends after echo'        => '{{ a }}{% extends "a" %}',
    'driver: extends after blank'       => "  \n{% extends \"a\" %}",
    'driver: child if'                  => '{% extends "a" %}{% if a %}x{% endif %}',
    'driver: child for'                 => '{% extends "a" %}{% for a in b %}x{% endfor %}',
    'driver: child switch'              => '{% extends "a" %}{% switch a %}{% endswitch %}',
    'driver: child set'                 => '{% extends "a" %}{% set a = 1 %}',
    'driver: child echo'                => '{% extends "a" %}{{ a }}',
    'driver: child raw'                 => '{% extends "a" %}x',
    'driver: child blank raw'           => "{% extends \"a\" %}\n  \n{% block a %}x{% endblock %}\n",
    'driver: child block raw'           => '{% extends "a" %}{% block a %}x{% endblock %}',
    'driver: child do'                  => '{% extends "a" %}{% do a %}',
    'driver: child macro'               => '{% extends "a" %}{% macro m() %}x{% endmacro %}',
    'driver: child include'             => '{% extends "a" %}{% include "b" %}',
    // `{% for ... if %}` counts as an opened if block and never closes it, so
    // the block level stays raised after the loop (cphalcon base.c quirk)
    'driver: for if then block'         => '{% for a in b if c %}x{% endfor %}{% block d %}{% endblock %}',
    'driver: for if then two blocks'    => '{% for a in b if c %}x{% endfor %}{% for e in f %}y{% endfor %}{% block d %}z{% endblock %}',
    'driver: for without if then block' => '{% for a in b %}x{% endfor %}{% block d %}{% endblock %}',
    'driver: for if inside child block' => '{% extends "a" %}{% block b %}{% for c in d if e %}x{% endfor %}{% endblock %}{% block f %}{% endblock %}',
    'driver: for if then echo in child' => '{% extends "a" %}{% block b %}{% for c in d if e %}x{% endfor %}{% endblock %}{{ g }}',

    // Statement-level syntax errors
    'statement: set empty'              => '{% set %}',
    'statement: set no value'           => '{% set a %}',
    'statement: set dangling assign'    => '{% set a = %}',
    'statement: set integer target'     => '{% set 1 = 1 %}',
    'statement: set call target'        => '{% set a() = 1 %}',
    'statement: for empty'              => '{% for %}',
    'statement: for no in'              => '{% for a %}',
    'statement: for no iterable'        => '{% for a in %}',
    'statement: for missing value'      => '{% for a, in b %}x{% endfor %}',
    'statement: macro empty'            => '{% macro %}',
    'statement: macro no parens'        => '{% macro a %}x{% endmacro %}',
    'statement: macro open paren'       => '{% macro a( %}',
    'statement: macro default ident'    => '{% macro a(b = c) %}x{% endmacro %}',
    'statement: macro default negative' => '{% macro a(b = -1) %}x{% endmacro %}',
    'statement: autoescape ident'       => '{% autoescape a %}x{% endautoescape %}',
    'statement: autoescape empty'       => '{% autoescape %}',
    'statement: include empty'          => '{% include %}',
    'statement: include with nothing'   => '{% include a with %}',
    'statement: call empty'             => '{% call %}',
    'statement: call no parens'         => '{% call a %}x{% endcall %}',
    'statement: verbatim unclosed'      => '{% verbatim %}x',
    'statement: verbatim bare'          => '{% verbatim %}',
    'statement: endverbatim alone'      => '{% endverbatim %}',
    'statement: block no name'          => '{% block %}x{% endblock %}',
    'statement: unknown keyword'        => '{% foo %}',
    'statement: echo in statement'      => '{% a %}',
    'statement: ternary missing else'   => '{{ a ? b }}',
    'statement: trailing dot'           => '{{ a.b.c. }}',
    'statement: break with expr'        => '{% break a %}',

    // Scanning errors
    'scan: dollar short'                => '{{ $ }}',
    'scan: dollar long'                 => '{{ a $ bcdefghijklmnopqrstuvwxyz }}',
    'scan: at sign'                     => '{{ a @ b }}',
    'scan: caret'                       => '{{ a ^ b }}',
    'scan: ampersand'                   => '{{ a & b }}',
    'scan: semicolon'                   => '{{ a; }}',
    'scan: trailing backslash dq'       => '{{ "abc\\',
    'scan: trailing backslash sq'       => "{{ 'abc\\",
    'scan: unterminated dq'             => '{{ "abc',
    'scan: unterminated sq'             => "{{ 'abc",
    'scan: unterminated long'           => '{{ "abcdefghijklmnopqrstuvwxyz',
    'scan: error on second line'        => "{{ a }}\n{{ $ }}",
    'scan: error after ignore'          => '{{ a      $ }}',

    // A raw fragment trimmed to nothing leaves an empty dangling value in
    // cphalcon, so the next error without a value of its own prints "()"
    'dangling: trimmed fragment'        => "{{ a -}}\n\n\n{{ }}b",
    'dangling: trimmed before open'     => '   {{-{{- with -}}   ',
    'dangling: comment then trimmed'    => '{# c #} {{-{{- a }}',
    'dangling: value token resets'      => "{{ a -}}\n{{ b ] }}",
    'dangling: trimmed then keyword'    => '{%- if a -%}  {%-] endif -%}',

    // Comments interacting with errors
    'comment: unterminated after if'    => '{% if a %}{# x',
    'comment: closed after if'          => '{% if a %}{# x #}',
    'comment: closed after if newline'  => "{% if a %}{# x #}\n",
];
