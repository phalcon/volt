<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit;

use Phalcon\Volt\Compiler;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

final class IsTagFactoryTest extends TestCase
{
    /**
     * @return array[]
     */
    public static function providerExamples(): array
    {
        return [
            [
                [],
                false,
            ],
            [
                [
                    'name' => [
                        'left' => [
                            'value' => 'tag',
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'name' => [
                        'left' => [
                            'value' => 'something',
                        ],
                    ],
                ],
                false,
            ],
            [
                [
                    'name' => [
                        'left' => [
                            'name' => [
                                'left' => [
                                    'value' => 'tag',
                                ],
                            ],
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'name' => [
                        'left' => [
                            'name' => [
                                'left' => [
                                    'value' => 'something',
                                ],
                            ],
                        ],
                    ],
                ],
                false,
            ],
        ];
    }

    /**
     * Tests Phalcon\Mvc\View\Engine\Volt :: isTagFactory()
     *
     * @dataProvider providerExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2024-07-31
     */
    public function testMvcViewEngineVoltIsTagFactory(
        array $source,
        bool $expected
    ): void {
        $parent   = new Compiler();
        $refected = new ReflectionObject($parent);
        $method   = $refected->getMethod('isTagFactory');
        $method->setAccessible(true);

        $actual = $method->invoke($parent, $source);
        $this->assertSame($expected, $actual);
    }
}
