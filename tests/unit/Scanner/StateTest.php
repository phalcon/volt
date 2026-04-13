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

use Phalcon\Volt\Scanner\State;
use PHPUnit\Framework\TestCase;

final class StateTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $state = new State('hello');

        $this->assertSame('hello', $state->getRawBuffer());
        $this->assertSame(5, $state->getStartLength());
        $this->assertSame('eval code', $state->getActiveFile());
        $this->assertSame(1, $state->getActiveLine());
        $this->assertSame(0, $state->getCursor());
        $this->assertSame(0, $state->getBlockLevel());
        $this->assertSame(0, $state->getExtendsMode());
        $this->assertSame(0, $state->getForLevel());
        $this->assertSame(0, $state->getForcedRawState());
        $this->assertSame(0, $state->getIfLevel());
        $this->assertSame(0, $state->getMacroLevel());
        $this->assertSame(0, $state->getOldIfLevel());
        $this->assertSame(0, $state->getStatementPosition());
        $this->assertSame(0, $state->getSwitchLevel());
        $this->assertSame('', $state->getRawFragment());
        $this->assertSame(0, $state->getRawBufferCursor());
        $this->assertNull($state->getActiveToken());
        $this->assertFalse($state->getWhitespaceControl());
    }

    public function testSettersAndGetters(): void
    {
        $state = new State('test');

        $state->setBlockLevel(2);
        $this->assertSame(2, $state->getBlockLevel());

        $state->setExtendsMode(1);
        $this->assertSame(1, $state->getExtendsMode());

        $state->setForLevel(3);
        $this->assertSame(3, $state->getForLevel());

        $state->setForcedRawState(1);
        $this->assertSame(1, $state->getForcedRawState());

        $state->setIfLevel(1);
        $this->assertSame(1, $state->getIfLevel());

        $state->setMacroLevel(1);
        $this->assertSame(1, $state->getMacroLevel());

        $state->setOldIfLevel(2);
        $this->assertSame(2, $state->getOldIfLevel());

        $state->setStatementPosition(5);
        $this->assertSame(5, $state->getStatementPosition());

        $state->setSwitchLevel(1);
        $this->assertSame(1, $state->getSwitchLevel());

        $state->setRawFragment('hello');
        $this->assertSame('hello', $state->getRawFragment());

        $state->appendToRawFragment(' world');
        $this->assertSame('hello world', $state->getRawFragment());

        $state->setActiveToken(42);
        $this->assertSame(42, $state->getActiveToken());

        $state->setWhitespaceControl(true);
        $this->assertTrue($state->getWhitespaceControl());
    }

    public function testIncrementMethods(): void
    {
        $state = new State('abc');

        $state->incrementBlockLevel();
        $this->assertSame(1, $state->getBlockLevel());

        $state->decrementBlockLevel();
        $this->assertSame(0, $state->getBlockLevel());

        $state->incrementIfLevel();
        $this->assertSame(1, $state->getIfLevel());

        $state->decrementIfLevel();
        $this->assertSame(0, $state->getIfLevel());

        $state->incrementForLevel();
        $this->assertSame(1, $state->getForLevel());

        $state->decrementForLevel();
        $this->assertSame(0, $state->getForLevel());

        $state->incrementMacroLevel();
        $this->assertSame(1, $state->getMacroLevel());

        $state->decrementMacroLevel();
        $this->assertSame(0, $state->getMacroLevel());

        $state->incrementSwitchLevel();
        $this->assertSame(1, $state->getSwitchLevel());

        $state->decrementSwitchLevel();
        $this->assertSame(0, $state->getSwitchLevel());

        $state->incrementStatementPosition();
        $this->assertSame(1, $state->getStatementPosition());

        $state->incrementForcedRawState();
        $this->assertSame(1, $state->getForcedRawState());

        $state->decrementForcedRawState();
        $this->assertSame(0, $state->getForcedRawState());
    }

    public function testGetPrevious(): void
    {
        $state = new State('hello');
        $state->setCursor(2);

        $this->assertSame('e', $state->getPrevious());
        $this->assertSame('h', $state->getPrevious(2));
        $this->assertNull($state->getPrevious(10));
    }

    public function testIncrementRawBufferCursor(): void
    {
        $state = new State('test');

        $this->assertSame(0, $state->getRawBufferCursor());
        $state->incrementRawBufferCursor();
        $this->assertSame(1, $state->getRawBufferCursor());
        $state->incrementRawBufferCursor();
        $this->assertSame(2, $state->getRawBufferCursor());
    }

    public function testSetActiveLine(): void
    {
        $state = new State('test');

        $this->assertSame(1, $state->getActiveLine());
        $state->setActiveLine(5);
        $this->assertSame(5, $state->getActiveLine());
    }

    public function testSetMarkerCursor(): void
    {
        $state = new State('hello world');

        $state->setMarkerCursor(3);
        $this->assertSame(3, $state->getCursor());
    }

    public function testSetRawBuffer(): void
    {
        $state = new State('original');

        $state->setRawBuffer('replacement');
        $this->assertSame('replacement', $state->getRawBuffer());
    }

    public function testSetRawBufferCursor(): void
    {
        $state = new State('test');

        $state->setRawBufferCursor(5);
        $this->assertSame(5, $state->getRawBufferCursor());
    }
}
