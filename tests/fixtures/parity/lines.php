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
 * Line numbers. Lemon stamps `line` when a rule is reduced, which happens only
 * once the NEXT token has been scanned - so a node's line is that of whatever
 * follows it (including newlines inside a following raw fragment, or the final
 * line at EOF). Newlines inside string literals are not counted at all.
 */

return [
    'if: multiline body'               => "{% if a %}\nx\n{% endif %}\ny",
    'if: elseif else multiline'        => "{% if a %}\n{% elseif b %}\n{% else %}\n{% endif %}",
    'if: trailing newlines'            => "{% if a %}x{% endif %}\n\n\n",
    'echo: operands on lines'          => "{{ a\n+\nb }}",
    'echo: trailing newline'           => "{{ a }}\n",
    'echo: trailing blank lines'       => "{{ a }}\n\n\n",
    'echo: after raw lines'            => "text\n\n{{ a }}",
    'echo: string with newline'        => "{{ 'multi\nline' }}\n{{ b }}",
    'echo: array on lines'             => "{{\n[\n1,\n2\n]\n}}",
    'echo: call args on lines'         => "{{ f(\na,\nb\n) }}",
    'for: blank lines'                 => "{% for x in y %}\n\n{{ x }}\n\n{% endfor %}",
    'for: key value elsefor'           => "{% for k, v in y if v %}\n{{ k }}\n{% elsefor %}\nnone\n{% endfor %}",
    'switch: case followed by lines'   => "{% switch a %}\n{% case \"Jim\" %}\n\nfoo\n{% endswitch %}",
    'switch: default on lines'         => "{% switch a %}\n{% case 1 %}\none\n{% default %}\nother\n{% endswitch %}",
    'set: two statements'              => "{% set a = 1 %}\n{% set b = 2 %}",
    'set: multi assignment lines'      => "{% set a = 1,\nb = 2,\nc = 3 %}",
    'comment: before echo'             => "{# comment\n\n #}{{ a }}",
    'comment: between raw'             => "a\n{# c\n #}\nb{{ c }}",
    'macro: params and body'           => "{% macro m(a, b = 1) %}\n{{ a }}\n{% endmacro %}",
    'block: empty then newline'        => "{% block b %}\n{% endblock %}\n",
    'autoescape: body'                 => "{% autoescape true %}\n{{ a }}\n{% endautoescape %}",
    'verbatim: body then newline'      => "{% verbatim %}\n{{ a }}\n{% endverbatim %}\n",
    'include: with then newline'       => "{% include 'a' with {'b': 1} %}\n",
    'do: return on lines'              => "{% do a() %}\n\n{% return b %}",
    'whitespace control: lines'        => "{%- if a -%}\n x \n{%- endif -%}\n",
    'call: body on lines'              => "{% call f(1) %}\nbody\n{% endcall %}\n",
    'ternary: on lines'                => "{{ a\n?\nb\n:\nc }}",
    'filter: on lines'                 => "{{ a\n|\nf\n}}",
    'raw only multiline'               => "a\nb\nc",
    'crlf raw and code'                => "a\r\n{{ b }}\r\n{% if c %}\r\nd\r\n{% endif %}",
];
