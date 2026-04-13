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

namespace Phalcon\Tests\Unit;

use Phalcon\Volt\Exception;
use PHPUnit\Framework\TestCase;

final class ExceptionTest extends TestCase
{
    public function testDefaultStatement(): void
    {
        $ex = new Exception('error');

        $this->assertSame([], $ex->getStatement());
        $this->assertSame('error', $ex->getMessage());
    }

    public function testWithStatement(): void
    {
        $statement = ['type' => 306, 'file' => 'test.volt', 'line' => 12];
        $ex        = new Exception('error', $statement, 0);

        $this->assertSame($statement, $ex->getStatement());
    }

    public function testExtendsBaseException(): void
    {
        $ex = new Exception('test');

        $this->assertInstanceOf(\Exception::class, $ex);
    }
}
