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

use Error;
use Phalcon\Volt\Scanner\Token;
use PHPUnit\Framework\TestCase;

final class TokenTest extends TestCase
{
    public function testDefaultsToEndOfInputWithoutValue(): void
    {
        $token = new Token();

        $this->assertSame([0, null], [$token->opcode, $token->value]);
    }

    public function testHoldsOpcodeAndValue(): void
    {
        $token = new Token(265, 'name');

        $this->assertSame([265, 'name'], [$token->opcode, $token->value]);
    }

    public function testIsReadonly(): void
    {
        $token = new Token(265, 'name');

        $this->expectException(Error::class);

        /** @phpstan-ignore-next-line - asserting the readonly guard */
        $token->opcode = 1;
    }
}
