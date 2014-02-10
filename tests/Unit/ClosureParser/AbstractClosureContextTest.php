<?php

namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\AbstractClosureContext;
use SuperClosure\Test\Unit\UnitTestBase;

class ConcreteClosureContext extends AbstractClosureContext {}

/**
 * @covers \SuperClosure\ClosureParser\AbstractClosureContext
 */
class AbstractClosureContextTest extends UnitTestBase
{
    public function testCanGetDataFromContext()
    {
        $context = new ConcreteClosureContext('foo', array('foo' => 'bar'), $this->getMockClosureBinding());

        $this->assertEquals('foo', $context->getCode());
        $this->assertEquals(array('foo' => 'bar'), $context->getVariables());
        $this->assertInstanceOf('SuperClosure\ClosureBinding', $context->getBinding());
    }
}
