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

use PHPUnit\Framework\TestCase;
use Phalcon\Volt\Scanner\Opcode;

final class OpcodeTest extends TestCase
{
    public function testKeyOpcodeValues(): void
    {
        $this->assertSame(1, Opcode::OPEN_DELIMITER->value);
        $this->assertSame(2, Opcode::COMMA->value);
        $this->assertSame(6, Opcode::AND->value);
        $this->assertSame(7, Opcode::OR->value);
        $this->assertSame(9, Opcode::IS->value);
        $this->assertSame(21, Opcode::PLUS->value);
        $this->assertSame(22, Opcode::MINUS->value);
        $this->assertSame(31, Opcode::IF->value);
        $this->assertSame(37, Opcode::FOR->value);
        $this->assertSame(38, Opcode::IDENTIFIER->value);
        $this->assertSame(51, Opcode::MACRO->value);
        $this->assertSame(54, Opcode::INTEGER->value);
        $this->assertSame(55, Opcode::STRING->value);
        $this->assertSame(56, Opcode::DOUBLE->value);
        $this->assertSame(79, Opcode::RAW_FRAGMENT->value);
    }

    public function testLabelFallsBackToName(): void
    {
        $this->assertSame('INTEGER', Opcode::INTEGER->label());
        $this->assertSame('IDENTIFIER', Opcode::IDENTIFIER->label());
        $this->assertSame('FOR', Opcode::FOR->label());
    }

    public function testLabelMultiCharOperators(): void
    {
        $this->assertSame('{%', Opcode::OPEN_DELIMITER->label());
        $this->assertSame('%}', Opcode::CLOSE_DELIMITER->label());
        $this->assertSame('{{', Opcode::OPEN_EDELIMITER->label());
        $this->assertSame('}}', Opcode::CLOSE_EDELIMITER->label());
        $this->assertSame('!=', Opcode::NOTEQUALS->label());
        $this->assertSame('===', Opcode::IDENTICAL->label());
        $this->assertSame('!==', Opcode::NOTIDENTICAL->label());
        $this->assertSame('<=', Opcode::LESSEQUAL->label());
        $this->assertSame('>=', Opcode::GREATEREQUAL->label());
        $this->assertSame('+=', Opcode::ADD_ASSIGN->label());
        $this->assertSame('-=', Opcode::SUB_ASSIGN->label());
        $this->assertSame('*=', Opcode::MUL_ASSIGN->label());
        $this->assertSame('/=', Opcode::DIV_ASSIGN->label());
        $this->assertSame('++', Opcode::INCR->label());
        $this->assertSame('--', Opcode::DECR->label());
    }

    public function testLabelSingleCharOperators(): void
    {
        $this->assertSame('+', Opcode::PLUS->label());
        $this->assertSame('-', Opcode::MINUS->label());
        $this->assertSame('*', Opcode::TIMES->label());
        $this->assertSame('/', Opcode::DIVIDE->label());
        $this->assertSame('%', Opcode::MOD->label());
        $this->assertSame('.', Opcode::DOT->label());
        $this->assertSame(',', Opcode::COMMA->label());
        $this->assertSame(':', Opcode::COLON->label());
        $this->assertSame('?', Opcode::QUESTION->label());
        $this->assertSame('~', Opcode::CONCAT->label());
        $this->assertSame('|', Opcode::PIPE->label());
        $this->assertSame('(', Opcode::PARENTHESES_OPEN->label());
        $this->assertSame(')', Opcode::PARENTHESES_CLOSE->label());
        $this->assertSame('[', Opcode::SBRACKET_OPEN->label());
        $this->assertSame(']', Opcode::SBRACKET_CLOSE->label());
        $this->assertSame('{', Opcode::CBRACKET_OPEN->label());
        $this->assertSame('}', Opcode::CBRACKET_CLOSE->label());
    }
}
