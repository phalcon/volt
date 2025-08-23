<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit;

use Phalcon\Volt\Utils;
use PHPUnit\Framework\TestCase;

final class UtilsTest extends TestCase
{
    /**
     * @dataProvider getUriProvider
     */
    public function testGetUri(string $input, string $expected): void
    {
        $this->assertSame($expected, Utils::getUri($input));
    }

    public static function getUriProvider(): array
    {
        return [
            ['/foo/bar/baz.txt', 'bar'],
            ['/foo/bar/', 'bar'],
            ['/foo/bar', 'foo'],
            ['foo/bar/baz', 'bar'],
            ['foo/bar', ''],
            ['foo', ''],
            ['', ''],
            ['/foo/', 'foo'],
            ['/', ''],
            ['\\foo\\bar\\baz.txt', 'bar'],
            ['foo\\bar\\baz', 'bar'],
        ];
    }

    /**
     * @dataProvider replacePathsProvider
     */
    public function testReplacePaths($pattern, $paths, $replacements, $expected): void
    {
        $this->assertSame($expected, Utils::replacePaths($pattern, $paths, $replacements));
    }

    public static function replacePathsProvider(): array
    {
        return [
            // Empty pattern returns false
            ['', [], [], false],
            // No paths, returns pattern without leading slash
            ['/abc/def', [], [], 'abc/def'],
            // Named placeholder replacement
            // Bug in phpunit 10.5.*, check later
            /*[
                '/{name}/profile',
                [1 => 'name'],
                ['name' => 'john'],
                'john/profile'
            ],*/
            // Positional placeholder replacement
            [
                '/(user)/profile',
                [1 => 'user'],
                ['user' => 'alice'],
                'alice/profile'
            ],
            // Sequence placeholder replacement
            [
                '/:user/profile',
                [1 => 'user'],
                ['user' => 'bob'],
                'bobprofile'
            ],
            // Multiple placeholders
            [
                '/{first}/:second/(third)',
                [1 => 'first', 2 => 'second', 3 => 'third'],
                ['first' => 'a', 'second' => 'b', 'third' => 'c'],
                'a/bc'
            ],
            // Placeholder with missing replacement (should remove placeholder, but slash remains)
            [
                '/{missing}/profile',
                [1 => 'missing'],
                [],
                '/profile'
            ],
            // Placeholder with invalid name (should remove placeholder, but slash remains)
            [
                '/{1invalid}/profile',
                [1 => '1invalid'],
                ['1invalid' => 'x'],
                '/profile'
            ],
            // Placeholder with allowed chars
            [
                '/{name_1}/profile',
                [1 => 'name_1'],
                ['name_1' => 'john'],
                'john/profile'
            ],
            // Leading slash removed if no paths
            [
                '/onlyslash',
                [],
                [],
                'onlyslash'
            ],
        ];
    }
}
