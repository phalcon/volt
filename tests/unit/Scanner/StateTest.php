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
use Phalcon\Volt\Scanner\State;
use PHPUnit\Framework\TestCase;

final class StateTest extends TestCase
{
    public function testCountersIncrement(): void
    {
        $state = (new State(''))
            ->incrementActiveLine()
            ->incrementActiveLine(3)
            ->incrementStatementPosition()
            ->incrementStatementPosition();

        $this->assertSame([5, 2], [$state->getActiveLine(), $state->getStatementPosition()]);
    }

    public function testSettersAreFluent(): void
    {
        $state = (new State(''))
            ->setCursor(3)
            ->setMode(Mode::VERBATIM)
            ->setActiveToken(46)
            ->setRawFragment('ab')
            ->appendToRawFragment('c')
            ->setVerbatim(true)
            ->setWhitespaceControl(true);

        $this->assertSame(
            [3, Mode::VERBATIM, 46, 'abc', true, true],
            [
                $state->getCursor(),
                $state->getMode(),
                $state->getActiveToken(),
                $state->getRawFragment(),
                $state->isVerbatim(),
                $state->getWhitespaceControl(),
            ]
        );
    }

    public function testStartsAtTheBeginningInRawMode(): void
    {
        $state = new State('{{ a }}');

        $this->assertSame(
            ['{{ a }}', 0, Mode::RAW, 1, null, 0, '', false, false],
            [
                $state->getCode(),
                $state->getCursor(),
                $state->getMode(),
                $state->getActiveLine(),
                $state->getActiveToken(),
                $state->getStatementPosition(),
                $state->getRawFragment(),
                $state->isVerbatim(),
                $state->getWhitespaceControl(),
            ]
        );
    }
}
