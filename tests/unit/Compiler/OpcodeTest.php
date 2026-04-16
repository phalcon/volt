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

namespace Phalcon\Tests\Unit\Compiler;

use Phalcon\Volt\Compiler\Opcode;
use PHPUnit\Framework\TestCase;

final class OpcodeTest extends TestCase
{
    public function testOpcodeValues(): void
    {
        // ASCII-value tokens
        $this->assertSame(43, Opcode::ADD->value);
        $this->assertSame(61, Opcode::ASSIGN->value);
        $this->assertSame(125, Opcode::CBRACKET_CLOSE->value);
        $this->assertSame(123, Opcode::CBRACKET_OPEN->value);
        $this->assertSame(126, Opcode::CONCAT->value);
        $this->assertSame(47, Opcode::DIV->value);
        $this->assertSame(46, Opcode::DOT->value);
        $this->assertSame(62, Opcode::GREATER->value);
        $this->assertSame(60, Opcode::LESS->value);
        $this->assertSame(37, Opcode::MOD->value);
        $this->assertSame(42, Opcode::MUL->value);
        $this->assertSame(33, Opcode::NOT->value);
        $this->assertSame(41, Opcode::PARENTHESES_CLOSE->value);
        $this->assertSame(40, Opcode::PARENTHESES_OPEN->value);
        $this->assertSame(124, Opcode::PIPE->value);
        $this->assertSame(63, Opcode::QUESTION->value);
        $this->assertSame(93, Opcode::SBRACKET_CLOSE->value);
        $this->assertSame(91, Opcode::SBRACKET_OPEN->value);
        $this->assertSame(45, Opcode::SUB->value);

        // Numeric-range tokens
        $this->assertSame(257, Opcode::IGNORE->value);
        $this->assertSame(258, Opcode::INTEGER->value);
        $this->assertSame(259, Opcode::DOUBLE->value);
        $this->assertSame(260, Opcode::STRING->value);
        $this->assertSame(261, Opcode::NULL->value);
        $this->assertSame(262, Opcode::FALSE->value);
        $this->assertSame(263, Opcode::TRUE->value);
        $this->assertSame(265, Opcode::IDENTIFIER->value);
        $this->assertSame(266, Opcode::AND->value);
        $this->assertSame(267, Opcode::OR->value);
        $this->assertSame(269, Opcode::COMMA->value);
        $this->assertSame(270, Opcode::LESSEQUAL->value);
        $this->assertSame(271, Opcode::GREATEREQUAL->value);
        $this->assertSame(272, Opcode::EQUALS->value);
        $this->assertSame(273, Opcode::NOTEQUALS->value);
        $this->assertSame(274, Opcode::IDENTICAL->value);
        $this->assertSame(275, Opcode::NOTIDENTICAL->value);
        $this->assertSame(276, Opcode::RANGE->value);
        $this->assertSame(277, Opcode::COLON->value);
        $this->assertSame(278, Opcode::POW->value);
        $this->assertSame(279, Opcode::INCR->value);
        $this->assertSame(280, Opcode::DECR->value);
        $this->assertSame(281, Opcode::ADD_ASSIGN->value);
        $this->assertSame(282, Opcode::SUB_ASSIGN->value);
        $this->assertSame(283, Opcode::MUL_ASSIGN->value);
        $this->assertSame(284, Opcode::DIV_ASSIGN->value);
        $this->assertSame(300, Opcode::IF->value);
        $this->assertSame(301, Opcode::ELSE->value);
        $this->assertSame(302, Opcode::ELSEIF->value);
        $this->assertSame(303, Opcode::ENDIF->value);
        $this->assertSame(304, Opcode::FOR->value);
        $this->assertSame(305, Opcode::ENDFOR->value);
        $this->assertSame(306, Opcode::SET->value);
        $this->assertSame(307, Opcode::BLOCK->value);
        $this->assertSame(308, Opcode::ENDBLOCK->value);
        $this->assertSame(309, Opcode::IN->value);
        $this->assertSame(310, Opcode::EXTENDS->value);
        $this->assertSame(311, Opcode::IS->value);
        $this->assertSame(312, Opcode::DEFINED->value);
        $this->assertSame(313, Opcode::INCLUDE->value);
        $this->assertSame(314, Opcode::CACHE->value);
        $this->assertSame(315, Opcode::ENDCACHE->value);
        $this->assertSame(316, Opcode::DO->value);
        $this->assertSame(317, Opcode::AUTOESCAPE->value);
        $this->assertSame(318, Opcode::ENDAUTOESCAPE->value);
        $this->assertSame(319, Opcode::CONTINUE->value);
        $this->assertSame(320, Opcode::BREAK->value);
        $this->assertSame(321, Opcode::ELSEFOR->value);
        $this->assertSame(322, Opcode::MACRO->value);
        $this->assertSame(323, Opcode::ENDMACRO->value);
        $this->assertSame(324, Opcode::WITH->value);
        $this->assertSame(325, Opcode::CALL->value);
        $this->assertSame(326, Opcode::ENDCALL->value);
        $this->assertSame(327, Opcode::RETURN->value);
        $this->assertSame(330, Opcode::OPEN_DELIMITER->value);
        $this->assertSame(331, Opcode::CLOSE_DELIMITER->value);
        $this->assertSame(332, Opcode::OPEN_EDELIMITER->value);
        $this->assertSame(333, Opcode::CLOSE_EDELIMITER->value);
        $this->assertSame(350, Opcode::FCALL->value);
        $this->assertSame(354, Opcode::EXPR->value);
        $this->assertSame(355, Opcode::QUALIFIED->value);
        $this->assertSame(356, Opcode::ENCLOSED->value);
        $this->assertSame(357, Opcode::RAW_FRAGMENT->value);
        $this->assertSame(358, Opcode::EMPTY_STATEMENT->value);
        $this->assertSame(359, Opcode::ECHO->value);
        $this->assertSame(360, Opcode::ARRAY->value);
        $this->assertSame(361, Opcode::ARRAYACCESS->value);
        $this->assertSame(362, Opcode::NOT_ISSET->value);
        $this->assertSame(363, Opcode::ISSET->value);
        $this->assertSame(364, Opcode::RESOLVED_EXPR->value);
        $this->assertSame(365, Opcode::SLICE->value);
        $this->assertSame(366, Opcode::TERNARY->value);
        $this->assertSame(367, Opcode::NOT_IN->value);
        $this->assertSame(368, Opcode::MINUS->value);
        $this->assertSame(369, Opcode::PLUS->value);
        $this->assertSame(380, Opcode::EMPTY->value);
        $this->assertSame(381, Opcode::EVEN->value);
        $this->assertSame(382, Opcode::ODD->value);
        $this->assertSame(383, Opcode::NUMERIC->value);
        $this->assertSame(384, Opcode::SCALAR->value);
        $this->assertSame(385, Opcode::ITERABLE->value);
        $this->assertSame(386, Opcode::ISEMPTY->value);
        $this->assertSame(387, Opcode::ISEVEN->value);
        $this->assertSame(388, Opcode::ISODD->value);
        $this->assertSame(389, Opcode::ISNUMERIC->value);
        $this->assertSame(390, Opcode::ISSCALAR->value);
        $this->assertSame(391, Opcode::ISITERABLE->value);
        $this->assertSame(392, Opcode::NOT_ISEMPTY->value);
        $this->assertSame(393, Opcode::NOT_ISEVEN->value);
        $this->assertSame(394, Opcode::NOT_ISODD->value);
        $this->assertSame(395, Opcode::NOT_ISNUMERIC->value);
        $this->assertSame(396, Opcode::NOT_ISSCALAR->value);
        $this->assertSame(397, Opcode::NOT_ISITERABLE->value);
        $this->assertSame(400, Opcode::RAW->value);
        $this->assertSame(401, Opcode::ENDRAW->value);
        $this->assertSame(411, Opcode::SWITCH->value);
        $this->assertSame(412, Opcode::CASE->value);
        $this->assertSame(413, Opcode::DEFAULT->value);
        $this->assertSame(414, Opcode::ENDSWITCH->value);
    }
}
