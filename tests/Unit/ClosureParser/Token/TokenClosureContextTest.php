<?php

namespace SuperClosure\Test\Unit\ClosureParser\Token;

use SuperClosure\ClosureParser\Token\TokenClosureContext;
use SuperClosure\Test\Unit\UnitTestBase;

/**
 * @covers \SuperClosure\ClosureParser\Token\TokenClosureContext
 */
class TokenClosureContextTest extends UnitTestBase
{
    public function testCanGetDataFromContext()
    {
       $context = new TokenClosureContext(
            '',
            array(),
            array(),
            $this->getMockClosureBinding()
        );

        $this->assertInternalType('array', $context->getTokens());
    }

    public function testTokenArrayIsValidated()
    {
        $this->setExpectedException('InvalidArgumentException');

        $context = new TokenClosureContext(
            '',
            array(),
            array('foo'),
            $this->getMockClosureBinding()
        );
    }
}
