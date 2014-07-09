<?php

namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\ClosureContext;
use SuperClosure\Test\Unit\UnitTestBase;

class ConcreteClosureContext extends ClosureContext {}

/**
 * @covers \SuperClosure\ClosureParser\ClosureContext
 */
class ClosureContextTest extends UnitTestBase
{
    public function testCanGetDataFromContext()
    {
        $context = new ConcreteClosureContext('foo', array('foo' => 'bar'), $this->getMockClosureBinding());

        $this->assertEquals('foo', $context->getCode());
        $this->assertEquals(array('foo' => 'bar'), $context->getVariables());
        $this->assertInstanceOf('SuperClosure\ClosureBinding', $context->getBinding());
    }
}
