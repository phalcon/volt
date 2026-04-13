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

namespace Phalcon\Tests\Unit\Scanner;

use Phalcon\Volt\Scanner\Token;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $token = new Token();

        $this->assertSame(0, $token->opcode);
        $this->assertNull($token->value);
        $this->assertSame(0, $token->length);
    }

    public function testConstructorWithAllArgs(): void
    {
        $token = new Token(42, 'hello', 5);

        $this->assertSame(42, $token->opcode);
        $this->assertSame('hello', $token->value);
        $this->assertSame(5, $token->length);
    }

    public function testLengthDefaultsToStringLengthWhenValueProvided(): void
    {
        $token = new Token(1, 'world');

        $this->assertSame(5, $token->length);
    }

    public function testIsReadonly(): void
    {
        $token = new Token(1, 'test', 4);

        $this->expectException(\Error::class);
        $token->opcode = 2; // @phpstan-ignore-line
    }
}
