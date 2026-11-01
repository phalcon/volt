# Changelog

All notable changes are documented here. The format is based on [Keep a Changelog][keep_a_changelog] and this project adheres to [Semantic Versioning][semantic_versioning].

## [Unreleased](https://github.com/phalcon/volt/releases/tag/vx.x.x) (2026-xx-xx)

### Changed

- The scanner and the parser are written by hand instead of being transliterated from cphalcon's re2c scanner and Lemon parser; `resources/files/parser.php` and the global `phvolt_*` functions are gone.
- `Phalcon\Volt\Parser\Parser::setDebug()` and `setDebugFile()` are deprecated; the debug file now receives a shift/reduce trace.
- `Phalcon\Volt\Scanner\State` and `Phalcon\Volt\Scanner\Token` only hold scanner state; `Phalcon\Volt\Scanner\Opcode` and `Phalcon\Volt\Parser\Status` were removed.

### Added

- Parity tests against ext-phalcon: a template corpus with goldens recorded from cphalcon (`tests/generate-golden.php`), `FixturesTest`, and `tests/differential.php`, run in CI.

### Fixed

Parsing now matches cphalcon exactly. Previously:

- syntax errors showed the previous token's value, and dropped empty, `"0"` or blank values: `unexpected token ~(id)` instead of `unexpected token ~`;
- scanning errors quoted one character instead of the rest of the template;
- deeply nested templates were accepted instead of failing with "Parsing failed, the template expression is nested too deeply";
- blank text between the blocks of a child template was kept instead of dropped;
- a NUL byte did not end the template;
- `{# 0 #}` ended the template.

[keep_a_changelog]: https://keepachangelog.com/en/1.1.0/
[semantic_versioning]: https://semver.org/spec/v2.0.0.html
