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

use Phalcon\Volt\Scanner\ScannerStatus;
use PHPUnit\Framework\TestCase;

final class ScannerStatusTest extends TestCase
{
    public function testValues(): void
    {
        $this->assertSame(-1, ScannerStatus::EOF->value);
        $this->assertSame(-2, ScannerStatus::ERR->value);
        $this->assertSame(-3, ScannerStatus::IMPOSSIBLE->value);
        $this->assertSame(0, ScannerStatus::OK->value);
    }

    public function testFrom(): void
    {
        $this->assertSame(ScannerStatus::EOF, ScannerStatus::from(-1));
        $this->assertSame(ScannerStatus::ERR, ScannerStatus::from(-2));
        $this->assertSame(ScannerStatus::IMPOSSIBLE, ScannerStatus::from(-3));
        $this->assertSame(ScannerStatus::OK, ScannerStatus::from(0));
    }

    public function testTryFromUnknown(): void
    {
        $this->assertNull(ScannerStatus::tryFrom(99));
        $this->assertNull(ScannerStatus::tryFrom(-99));
    }
}
