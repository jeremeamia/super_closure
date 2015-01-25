<?php namespace SuperClosure\Test\Unit\Analyzer;

use SuperClosure\Analyzer\Token;

/**
 * @covers \SuperClosure\Analyzer\Token
 */
class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function testCanInstantiateLiteralToken()
    {
        $token = new Token('{');

        $this->assertEquals('{', $token->code);
        $this->assertNull($token->value);
        $this->assertNull($token->name);
        $this->assertNull($token->line);
        $this->assertEquals('{', (string)$token);
    }

    public function testCanInstantiateTokenWithParts()
    {
        $token = new Token('function', T_FUNCTION, 2);

        $this->assertEquals('function', $token->code);
        $this->assertEquals(T_FUNCTION, $token->value);
        $this->assertEquals('T_FUNCTION', $token->name);
        $this->assertEquals(2, $token->line);
    }

    public function testCanInstantiateTokenFromTokenizerOutput()
    {
        $token = new Token([T_FUNCTION, 'function', 2]);

        $this->assertEquals('function', $token->code);
        $this->assertEquals(T_FUNCTION, $token->value);
        $this->assertEquals('T_FUNCTION', $token->name);
        $this->assertEquals(2, $token->line);
    }

    public function testCanCheckIfTokenMatchesValue()
    {
        $token = new Token([T_FUNCTION, 'function', 2]);

        $this->assertTrue($token->is(T_FUNCTION));
        $this->assertTrue($token->is('function'));
        $this->assertFalse($token->is('cat'));
    }
}
