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

use Phalcon\Volt\Scanner\Mode;
use PHPUnit\Framework\TestCase;

final class ModeTest extends TestCase
{
    public function testModeValues(): void
    {
        $this->assertSame(1, Mode::CODE->value);
        $this->assertSame(2, Mode::COMMENT->value);
        $this->assertSame(0, Mode::RAW->value);
    }
}
