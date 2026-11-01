# Volt: Template Engine

[![Latest Version][packagist-version-badge]][packagist-version-link]
[![PHP Version][php-version-badge]][packagist-version-link]
[![Total Downloads][packagist-downloads-badge]][packagist-downloads-link]
[![License][license-badge]][license-link]

[![Phalcon Volt CI][phalcon-ci-badge]][phalcon-ci-link]
[![Quality Gate Status][sonar-quality-badge]][sonar-link]
[![Coverage][sonar-coverage-badge]][sonar-link]
[![PDS Skeleton][pds-skeleton-badge]][pds-skeleton-link]

[![Discord][discord-badge]][discord-link]
[![Contributors][contributors-badge]][contributors-link]
[![OpenCollective Backers][oc-backers-badge]][backers-link]
[![OpenCollective Sponsors][oc-sponsors-badge]][sponsors-link]

This repository consists of the Volt parser, for the Volt Template Engine used by the Phalcon Framework.

## What this is

Phalcon v6 is a pure-PHP rewrite of [cphalcon](https://github.com/phalcon/cphalcon),
the C extension behind Phalcon v5. This package is the Volt template parser for
v6, and it is a behavioural port of cphalcon's: the same intermediate
representation, key order and integer types included, the same line numbers,
the same error messages on the same tokens, and the same limit on how deeply a
template may nest. A template that parses (or fails) one way under v5 parses
(or fails) the same way here.

The scanner and the parser are written by hand. cphalcon generates its own from
a re2c scanner and a Lemon grammar; earlier versions of this package carried PHP
transliterations of those generated artefacts.

## Requirements

* PHP `>= 8.1 < 9.0`
* `ext-ctype`, `ext-mbstring`

## Installation

```bash
composer require phalcon/volt
```

## Usage

```php
use Phalcon\Volt\Parser\Parser;

$statements = (new Parser())->parse('{% for user in users %}{{ user.name }}{% endfor %}', 'users.volt');
```

`parse()` returns the list of statements, or throws `Phalcon\Volt\Exception` with
cphalcon's message. `Phalcon\Volt\Compiler` turns the statements into PHP; the
node `type` values are the cases of `Phalcon\Volt\Compiler\Opcode`.

## How it works

| Class | Role | cphalcon counterpart |
|---|---|---|
| `Scanner\Scanner` | Turns the template into tokens: raw text, comments, verbatim blocks, and the tokens inside `{{ }}` / `{% %}` | `scanner.re` |
| `Parser\TokenStream` | Feeds tokens to the parser one at a time, running the template checks (child templates, nested blocks, `extends` first, ...) | the token loop in `base.c` |
| `Parser\Parser` | Statements, by recursive descent - one method per grammar rule | `parser.php.lemon` |
| `Parser\ExpressionParser` | Expressions, by precedence climbing over the grammar's own precedence table | `parser.php.lemon` |
| `Parser\Cursor` | One token of lookahead plus an emulation of Lemon's parser stack | the generated `parser.c` |
| `Parser\Ast` | Builds the nodes | `parser.php.inc.h` |

Tokens are scanned on demand rather than up front, because cphalcon checks each
token before parsing it: a check on a later token must not pre-empt a syntax
error that cphalcon reports on an earlier one.

## Compatibility notes

Everything below is cphalcon's behaviour, reproduced on purpose and pinned by the
parity tests.

* **Operator precedence follows the grammar, not intuition.** `* / %` bind
  *looser* than `+ - ~`, so `1 + 2 * 3` is `(1 + 2) * 3`. Prefix `!` binds
  tighter than `[]` and `|`: `!a[0]` is `(!a)[0]`. `not in` binds much tighter
  than `in`, and the ternary groups to the left.
* **`**` and `//` are two tokens each**: `a * * b` is a power, `a / / b` a
  modulo.
* **Line numbers are those of the next token.** A node's `line` is where the
  parser was when it finished the node, which is after reading the token that
  follows it - including any newlines in raw text in between.
* **Nesting fails closed** with "Parsing failed, the template expression is
  nested too deeply" at exactly the depth cphalcon's 100-entry Lemon stack
  overflows (for example 97 nested parentheses, or 24 nested `{% if %}`).
* **Error messages keep cphalcon's token names**: `!` for `not`, `%%` for `%`,
  `NOT EQUALS` for `!=` / `<>` / `is not`, `UNKNOWN` for `|`, `null`, `true`,
  `false` and a few keywords. The token's value is shown only for value tokens,
  verbatim: `STRING()` for `""`.
* **An error on the last byte reads as end of input**: `{{ 1 + }}` reports
  "unexpected EOF", followed by the blocks left open.
* **`{% for x in y if cond %}` counts as an opened `if`** that is never closed;
  a `{% block %}` later in the same template is then reported as nested.
* **A NUL byte ends the template**, as it ends a C string.
* **Values are not unescaped and numbers stay strings.**

## Deliberate differences from cphalcon

* `{% cache %}` / `{% endcache %}` were removed: `cache` and `endcache` are plain
  identifiers here.
* In two situations cphalcon's syntax-error message prints freed memory (after a
  blank raw fragment of a child template is dropped, and after `{% default %}`).
  This package prints no value there. Where the freed value is empty, cphalcon
  deterministically prints `()`, and so does this package.

## Development

```bash
composer install
composer test-unit     # PHPUnit
composer analyze       # PHPStan, level max
composer cs            # PHP_CodeSniffer
composer cs-fixer-fix  # php-cs-fixer
```

### Verifying against the C parser

The parity corpus in `tests/fixtures/parity/*.php` lists templates: the snippets
of cphalcon's, phalcon's and this package's test suites
(`tests/harvest-corpus.php` collects them) plus hand-written groups for
precedence, line numbers, errors, scanner edge cases and nesting depth.

* `tests/generate-golden.php` runs every template through ext-phalcon and
  records the outcome in `tests/fixtures/parity/*.json`. It refuses to run
  without the extension, so the goldens always come from cphalcon.
* `tests/unit/Parser/FixturesTest.php` replays the goldens against this package.
* `tests/differential.php` compares both implementations live. It exits quietly
  without the extension.

```bash
php -d extension=phalcon tests/generate-golden.php   # after changing the corpus
php -d extension=phalcon tests/differential.php
```

Deliberate differences are listed, with the expected outcome and a reason, in
`tests/fixtures/parity/allow-list.php`.

## Why a hand-written parser

The re2c DFA and the Lemon tables are build artefacts: thousands of lines that
nobody can review, that hide the grammar, and that had drifted from cphalcon in
several places (error values, scanner errors, NUL bytes, the nesting limit,
child-template whitespace). The grammar itself is small - 154 rules - and every
shift/reduce decision Lemon makes comes from a 15-level precedence table. A
precedence-climbing parser over that table, plus a counter that mirrors Lemon's
stack, reproduces the generated parser exactly while staying readable: each
parser method quotes the grammar rules it implements.

<!-- Badges -->
[packagist-version-badge]:   https://img.shields.io/packagist/v/phalcon/volt?include_prereleases&style=flat-square
[packagist-version-link]:    https://packagist.org/packages/phalcon/volt
[packagist-downloads-badge]: https://img.shields.io/packagist/dt/phalcon/volt?style=flat-square
[packagist-downloads-link]:  https://packagist.org/packages/phalcon/volt/stats
[php-version-badge]:          https://img.shields.io/packagist/php-v/phalcon/volt?style=flat-square
[license-badge]:             https://img.shields.io/github/license/phalcon/volt?style=flat-square
[license-link]:              LICENSE

[phalcon-ci-badge]:          https://github.com/phalcon/volt/actions/workflows/main.yml/badge.svg
[phalcon-ci-link]:           https://github.com/phalcon/volt/actions/workflows/main.yml
[sonar-quality-badge]:       https://sonarcloud.io/api/project_badges/measure?project=phalcon_volt&metric=alert_status
[sonar-coverage-badge]:      https://sonarcloud.io/api/project_badges/measure?project=phalcon_volt&metric=coverage
[sonar-link]:                https://sonarcloud.io/summary/new_code?id=phalcon_volt
[pds-skeleton-badge]:        https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square
[pds-skeleton-link]:         https://github.com/php-pds/skeleton

[discord-badge]:             https://img.shields.io/discord/310910488152375297?label=Discord&logo=discord&style=flat-square
[discord-link]:              https://phalcon.io/discord
[contributors-badge]:        https://img.shields.io/github/contributors/phalcon/volt?style=flat-square
[contributors-link]:         https://github.com/phalcon/volt/graphs/contributors
[oc-backers-badge]:          https://img.shields.io/opencollective/backers/phalcon?style=flat-square
[oc-sponsors-badge]:         https://img.shields.io/opencollective/sponsors/phalcon?style=flat-square
[backers-link]:              https://opencollective.com/phalcon
[sponsors-link]:             https://opencollective.com/phalcon
