<?php namespace SuperClosure\Test\Unit\ClosureParser\Token;

use SuperClosure\ClosureParser\Token\Token;
use SuperClosure\Test\Unit\UnitTestBase;

class TokenTest extends UnitTestBase
{
    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::__construct
     */
    public function testCanInstantiateTokenWithConstructor()
    {
        $token = new Token('function', T_FUNCTION, 2);
        $this->assertInstanceOf('SuperClosure\ClosureParser\Token\Token', $token);
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::fromTokenData
     */
    public function testCanInstantiateTokenWithFactory()
    {
        $token = Token::fromTokenData(array(T_FUNCTION, 'function', 2));
        $this->assertInstanceOf('SuperClosure\ClosureParser\Token\Token', $token);

        $token = Token::fromTokenData('{');
        $this->assertInstanceOf('SuperClosure\ClosureParser\Token\Token', $token);

        $this->setExpectedException('InvalidArgumentException');
        $token = Token::fromTokenData(100);
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::__construct
     */
    public function testInstantiationFailsOnBadValue()
    {
        $this->setExpectedException('InvalidArgumentException');
        $token = new Token('function', T_FUNCTION, 'derp');
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::__construct
     */
    public function testInstantiationFailsOnBadLine()
    {
        $this->setExpectedException('InvalidArgumentException');
        $token = new Token('function', 'derp');
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::getName
     */
    public function testGettingTheNameReturnsStringForNormalTokens()
    {
        $token = new Token('function', T_FUNCTION, 2);
        $this->assertEquals('T_FUNCTION', $token->getName());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::getName
     */
    public function testGettingTheNameReturnsNullForLiteralTokens()
    {
        $token = new Token('{');
        $this->assertNull($token->getName());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::getCode
     */
    public function testGettingTheCodeReturnsStringOfCodeForAnyTokens()
    {
        $token = new Token('function', T_FUNCTION, 2);
        $this->assertEquals('function', $token->getCode());

        $token = new Token('{');
        $this->assertEquals('{', $token->getCode());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::getLine
     */
    public function testGettingTheLineReturnsAnIntegerForNormalTokens()
    {
        $token = new Token('function', T_FUNCTION, 2);
        $this->assertEquals(2, $token->getLine());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::getLine
     */
    public function testGettingTheLineReturnsNullForLiteralTokens()
    {
        $token = new Token('{');
        $this->assertNull($token->getLine());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::getValue
     */
    public function testGettingTheValueReturnsAnIntegerForNormalTokens()
    {
        $token = new Token('function', T_FUNCTION, 2);
        $this->assertEquals(T_FUNCTION, $token->getValue());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::getValue
     */
    public function testGettingTheValueReturnsNullForLiteralTokens()
    {
        $token = new Token('{');
        $this->assertNull($token->getValue());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::isOpeningBrace
     */
    public function testOpeningBracesAreIdentifiedCorrectly()
    {
        $token = new Token('}');
        $this->assertFalse($token->isOpeningBrace());

        $token = new Token('{');
        $this->assertTrue($token->isOpeningBrace());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::isClosingBrace
     */
    public function testClosingBracesAreIdentifiedCorrectly()
    {
        $token = new Token('{');
        $this->assertFalse($token->isClosingBrace());

        $token = new Token('}');
        $this->assertTrue($token->isClosingBrace());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::isOpeningParenthesis
     */
    public function testOpeningParenthesesAreIdentifiedCorrectly()
    {
        $token = new Token(')');
        $this->assertFalse($token->isOpeningParenthesis());

        $token = new Token('(');
        $this->assertTrue($token->isOpeningParenthesis());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::isClosingParenthesis
     */
    public function testClosingParenthesesAreIdentifiedCorrectly()
    {
        $token = new Token('(');
        $this->assertFalse($token->isClosingParenthesis());

        $token = new Token(')');
        $this->assertTrue($token->isClosingParenthesis());
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::matches
     */
    public function testTokensAreIdentifiedCorrectlyByCodeOrValue()
    {
        $token = new Token('function', T_FUNCTION, 2);

        $this->assertTrue($token->matches(T_FUNCTION));
        $this->assertTrue($token->matches('function'));
        $this->assertFalse($token->matches(T_VARIABLE));
        $this->assertFalse($token->matches('foo'));
    }

    /**
     * @covers \SuperClosure\ClosureParser\Token\Token::__toString
     */
    public function testConvertingToStringReturnsTheTokenCode()
    {
        $token = new Token('function', T_FUNCTION, 2);
        $this->assertEquals((string) $token, $token->getCode());
    }
}
