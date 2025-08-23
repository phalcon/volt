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

namespace Phalcon\Volt;

/**
 * PHP adaptation of Phalcon's ext/phalcon/mvc/url/utils.c (commit 7a3b54d).
 *
 * Functions:
 *  - Utils::getUri(string $path): string
 *      Returns the substring between the *last two* directory separators in $path.
 *      If input is not a string or fewer than two separators are present, returns ''.
 *
 *  - Utils::replacePaths($pattern, $paths, $replacements)
 *      Replaces placeholders in $pattern using $paths (1-indexed map) and $replacements.
 *      Mirrors the C behavior:
 *        * Returns null + E_USER_WARNING if arg types are invalid.
 *        * Returns false if $pattern is an empty string.
 *        * If $paths is empty, returns $pattern without a leading slash (if present).
 *        * Recognizes three placeholder forms:
 *            - {name[:...]}  (named) — only letters/digits/-/_ allowed; name must start with a letter.
 *            - (...)         (positional/capturing group)
 *            - :placeholder  (sequence of [a-z] after ':', positional)
 *        * Each encountered placeholder advances a 1-based position counter ($position).
 *          Replacement for a placeholder is only attempted if $paths[$position] exists.
 *          If a replacement is not found, the placeholder is removed (not kept literally).
 */
final class Utils
{
    /**
     * Return the substring between the last two directory separators ('/' or '\').
     * e.g. "/foo/bar/baz.txt" => "bar"
     */
    public static function getUri(string $path): string
    {
        $len = strlen($path);
        if ($len === 0) {
            return '';
        }

        $found = 0;
        $mark  = 0;

        for ($i = $len - 1; $i >= 0; $i--) {
            $ch = $path[$i];
            if ($ch === '/' || $ch === '\\') {
                $found++;
                if ($found === 1) {
                    // index of char before the last separator
                    $mark = $i - 1;
                } else {
                    // second separator found: return text between them
                    $start  = $i + 1;               // after this separator
                    $length = $mark - $i;           // up to char before last separator
                    return $length > 0 ? substr($path, $start, $length) : '';
                }
            }
        }

        return '';
    }

    /**
     * Port of phalcon_replace_paths().
     *
     * @param mixed $pattern
     * @param mixed $paths
     * @param mixed $replacements
     * @return string|false|null
     */
    public static function replacePaths(string $pattern, array $paths, array $replacements): bool|string|null
    {
        if ($pattern === '') {
            return false;
        }

        $length  = strlen($pattern);
        $i       = 0;
        $out     = '';

        // Position counter is 1-based (matches the C code)
        $position = 1;

        // Remove leading slash from the output if present (C code skips it)
        if ($length > 0 && $pattern[0] === '/') {
            $i = 1;
        }

        // If there are no paths, short-circuit by returning the (optionally) trimmed pattern
        if (count($paths) === 0) {
            return substr($pattern, $i);
        }

        $bracketCount        = 0; // {...}
        $parenthesesCount    = 0; // (...)
        $lookingPlaceholder  = false; // for ':placeholder'
        $intermediate        = 0; // chars seen since entering a placeholder
        $marker              = 0; // index of '{' or '(' or ':'

        for (; $i < $length; $i++) {
            $ch = $pattern[$i];
            if ($ch === "\0") {
                break;
            }

            // Handle {...} (named)
            if ($parenthesesCount === 0 && !$lookingPlaceholder) {
                if ($ch === '{') {
                    if ($bracketCount === 0) {
                        $marker = $i;
                        $intermediate = 0;
                    }
                    $bracketCount++;
                } elseif ($ch === '}') {
                    $bracketCount--;
                    if ($intermediate > 0 && $bracketCount === 0) {
                        $inner = substr($pattern, $marker + 1, $i - $marker - 1);
                        $replace = self::replaceMarker(true, $paths, $replacements, $position, $inner);
                        if ($replace !== null) {
                            $out .= $replace;
                        }
                        // Skip appending the '}' literally, just continue
                        continue;
                    }
                }
            }

            // Handle (...) (positional)
            if ($bracketCount === 0 && !$lookingPlaceholder) {
                if ($ch === '(') {
                    if ($parenthesesCount === 0) {
                        $marker = $i;
                        $intermediate = 0;
                    }
                    $parenthesesCount++;
                } elseif ($ch === ')') {
                    $parenthesesCount--;
                    if ($intermediate > 0 && $parenthesesCount === 0) {
                        $inner = substr($pattern, $marker + 1, $i - $marker - 1);
                        $replace = self::replaceMarker(false, $paths, $replacements, $position, $inner);
                        if ($replace !== null) {
                            $out .= $replace;
                        }
                        continue;
                    }
                }
            }

            // Handle :placeholder (letters a–z only)
            if ($bracketCount === 0 && $parenthesesCount === 0) {
                if ($lookingPlaceholder) {
                    if ($intermediate > 0) {
                        if ($ch < 'a' || $ch > 'z' || $i === ($length - 1)) {
                            // End of placeholder word (or end of string): perform replacement
                            $replace = self::replaceMarker(false, $paths, $replacements, $position, '');
                            if ($replace !== null) {
                                $out .= $replace;
                            }
                            $lookingPlaceholder = false;
                            continue;
                        }
                    }
                } else {
                    if ($ch === ':') {
                        $lookingPlaceholder = true;
                        $marker = $i;
                        $intermediate = 0;
                    }
                }
            }

            if ($bracketCount > 0 || $parenthesesCount > 0 || $lookingPlaceholder) {
                $intermediate++;
            } else {
                $out .= $ch;
            }
        }

        return $out;
    }

    /**
     * Helper that replicates phalcon_replace_marker().
     *
     * - For named placeholders ({name[:...]}) we validate name and then look for
     *   $replacements[$name], but only if $paths[$position] exists.
     * - For positional placeholders ((...) or :placeholder) we look up the key name
     *   via $paths[$position] (must be a string) and then fetch $replacements[$key].
     * - Whether replacement is found or not, if the placeholder was valid, $position++
     *   happens exactly once per encountered placeholder.
     *
     * @param bool  $named
     * @param array $paths
     * @param array $replacements
     * @param int   $position (by reference; 1-based)
     * @param string $raw The inner text (for named and (...) cases). For :placeholder it is unused.
     * @return string|null
     */
    private static function replaceMarker(bool $named, array $paths, array $replacements, &$position, string $raw): ?string
    {
        $notValid = false;
        $item     = null; // variable name to use (for named)

        if ($named) {
            $item = $raw;
            $length = strlen($item);

            if ($length === 0) {
                $notValid = true;
            } else {
                $variable = null;

                for ($j = 0; $j < $length; $j++) {
                    $ch = $item[$j];

                    // first char must be a letter
                    if ($j === 0 && !ctype_alpha($ch)) {
                        $notValid = true;
                        break;
                    }

                    // allowed: letters, digits, '-', '_', ':'
                    if (ctype_alpha($ch) || ctype_digit($ch) || $ch === '-' || $ch === '_' || $ch === ':') {
                        if ($ch === ':') {
                            // take only the part before ':'
                            $variable = substr($item, 0, $j);
                            break;
                        }
                    } else {
                        $notValid = true;
                        break;
                    }
                }

                if ($variable !== null) {
                    $item = $variable;
                }
            }
        }

        if (!$notValid) {
            if (array_key_exists($position, $paths)) {
                if ($named) {
                    if ($item !== null && array_key_exists($item, $replacements)) {
                        $val = $replacements[$item];
                        $position++; // increment exactly once on success
                        return is_string($val) ? $val : (string) $val;
                    }
                } else {
                    $zv = $paths[$position] ?? null;
                    if (is_string($zv) && array_key_exists($zv, $replacements)) {
                        $tmp = $replacements[$zv];
                        $position++; // increment exactly once on success
                        return is_string($tmp) ? $tmp : (string) $tmp;
                    }
                }
            }

            // If valid but no replacement produced (or $paths[$position] missing), advance position
            $position++;
        }

        return null;
    }
}
