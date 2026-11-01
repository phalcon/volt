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

namespace Phalcon\Tests\Unit\Parser;

use Phalcon\Volt\Compiler\Opcode;
use Phalcon\Volt\Parser\Parser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_is_list;
use function implode;
use function in_array;
use function is_array;

/**
 * Operator grouping, written as s-expressions so the counter-intuitive parts
 * of the Lemon precedence table (parser.php.lemon lines 22-36) are readable:
 * `*` binds looser than `+`, prefix `!` binds tighter than `[`, `not in` binds
 * tighter than `in`, and the ternary groups to the left.
 *
 * The exact ASTs are pinned against ext-phalcon by FixturesTest
 * (`precedence` group); this test documents the shape.
 */
final class PrecedenceTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function groupingProvider(): array
    {
        return [
            'plus binds tighter than times'    => ['1 + 2 * 3', '(MUL (ADD 1 2) 3)'],
            'times then plus'                  => ['1 * 2 + 3', '(MUL 1 (ADD 2 3))'],
            'access binds tighter than plus'   => ['a + b[0]', '(ADD a (ARRAYACCESS b 0))'],
            'access binds tighter than times'  => ['a * b[0]', '(MUL a (ARRAYACCESS b 0))'],
            'dot binds tighter than access'    => ['a.b[0]', '(ARRAYACCESS (DOT a b) 0)'],
            'dot binds tighter than call'      => ['a.b(c)', '(FCALL (DOT a b) c)'],
            'not binds tighter than access'    => ['!a[0]', '(ARRAYACCESS (NOT a) 0)'],
            'dot binds tighter than not'       => ['!a.b', '(NOT (DOT a b))'],
            'call binds tighter than not'      => ['!a(x)', '(NOT (FCALL a x))'],
            'not binds tighter than pipe'      => ['!a|f', '(PIPE (NOT a) f)'],
            'dot binds tighter than minus'     => ['-a.b', '(MINUS (DOT a b))'],
            'minus binds tighter than times'   => ['-a * b', '(MUL (MINUS a) b)'],
            'call binds tighter than pipe'     => ['a|f(x)', '(PIPE a (FCALL f x))'],
            'pipe binds tighter than access'   => ['a|f[0]', '(ARRAYACCESS (PIPE a f) 0)'],
            'not in binds tighter than plus'   => ['a not in b + c', '(ADD (NOT_IN a b) c)'],
            'plus then not in'                 => ['a + b not in c', '(ADD a (NOT_IN b c))'],
            'in binds looser than plus'        => ['a in b + c', '(IN a (ADD b c))'],
            'ternary groups left'              => ['a ? b : c ? d : e', '(TERNARY (TERNARY a b c) d e)'],
            'ternary middle is full'           => ['a ? b ? c : d : e', '(TERNARY a (TERNARY b c d) e)'],
            'range binds looser than plus'     => ['a .. b + c', '(RANGE a (ADD b c))'],
            'is defined then and'              => ['a is defined and b', '(AND (ISSET a) b)'],
            'is binds looser than plus'        => ['a is b + c', '(IS a (ADD b c))'],
            'concat binds tighter than eq'     => ['a ~ b == c', '(EQUALS (CONCAT a b) c)'],
            'pow is left associative'          => ['2**3**2', '(POW (POW 2 3) 2)'],
            'plus binds tighter than pow'      => ['2 + 3 ** 4', '(POW (ADD 2 3) 4)'],
            'not over not in'                  => ['not a not in b', '(NOT (NOT_IN a b))'],
            'not in is right associative'      => ['a not in b not in c', '(NOT_IN a (NOT_IN b c))'],
            'not in binds tighter than access' => ['a not in b[0]', '(ARRAYACCESS (NOT_IN a b) 0)'],
            'call binds tighter than not in'   => ['a not in b(1)', '(NOT_IN a (FCALL b 1))'],
            'incr binds tighter than minus'    => ['-a++', '(MINUS (INCR a))'],
            'dot binds tighter than incr'      => ['a.b++', '(INCR (DOT a b))'],
            'dot takes a prefix operand'       => ['a.-b*c', '(MUL (DOT a (MINUS b)) c)'],
            'test applies to comparison'       => ['x == a is defined', '(ISSET (EQUALS x a))'],
            'floor division is mod'            => ['a // b', '(MOD a b)'],
            'and or same level'                => ['a or b and c', '(AND (OR a b) c)'],
        ];
    }

    /**
     * Renders an expression node as `(OPCODE child...)`, children in AST key
     * order; literals render as their value.
     */
    private static function sexpr(mixed $node): string
    {
        if (!is_array($node)) {
            return (string) $node;
        }

        if (isset($node['value'])) {
            return $node['type'] === Opcode::STRING->value ? '"' . $node['value'] . '"' : (string) $node['value'];
        }

        if (!isset($node['type'])) {
            return (isset($node['name']) ? $node['name'] . ': ' : '') . self::sexpr($node['expr']);
        }

        $parts = [Opcode::from($node['type'])->name];

        foreach ($node as $key => $child) {
            if (in_array($key, ['type', 'file', 'line'], true)) {
                continue;
            }

            if (is_array($child) && array_is_list($child)) {
                foreach ($child as $item) {
                    $parts[] = self::sexpr($item);
                }

                continue;
            }

            $parts[] = self::sexpr($child);
        }

        return '(' . implode(' ', $parts) . ')';
    }

    #[DataProvider('groupingProvider')]
    public function testGrouping(string $expression, string $expected): void
    {
        $statements = (new Parser())->parse('{{ ' . $expression . ' }}');

        $this->assertSame($expected, self::sexpr($statements[0]['expr']));
    }
}
