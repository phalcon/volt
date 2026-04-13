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

use Phalcon\Volt\Parser\Status;
use Phalcon\Volt\Scanner\State;
use Phalcon\Volt\Scanner\Token;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    public function testDefaultStatus(): void
    {
        $state  = new State('test');
        $status = new Status($state);

        $this->assertSame(Status::PHVOLT_PARSING_OK, $status->getStatus());
        $this->assertNull($status->getSyntaxError());
        $this->assertNull($status->getToken());
        $this->assertSame($state, $status->getState());
    }

    public function testSetStatus(): void
    {
        $state  = new State('test');
        $status = new Status($state);

        $result = $status->setStatus(Status::PHVOLT_PARSING_FAILED);
        $this->assertSame(Status::PHVOLT_PARSING_FAILED, $status->getStatus());
        $this->assertInstanceOf(Status::class, $result);
    }

    public function testSetSyntaxError(): void
    {
        $state  = new State('test');
        $status = new Status($state);

        $status->setSyntaxError('Unexpected token');
        $this->assertSame('Unexpected token', $status->getSyntaxError());
    }

    public function testSetToken(): void
    {
        $state  = new State('test');
        $status = new Status($state);
        $token  = new Token(42, 'hello');

        $status->setToken($token);
        $this->assertSame($token, $status->getToken());
    }

    public function testConstants(): void
    {
        $this->assertSame(0, Status::PHVOLT_PARSING_FAILED);
        $this->assertSame(1, Status::PHVOLT_PARSING_OK);
    }
}
