<?php

namespace SuperClosure\Test\Unit;

use SuperClosure\SuperClosure;

/**
 * Class SuperClosureTest
 */
class SuperClosureTest extends UnitTestBase
{
    /**
     * @covers \SuperClosure\SuperClosure::__construct
     * @covers \SuperClosure\SuperClosure::getClosure
     * @covers \SuperClosure\SuperClosure::getReflection
     * @covers \SuperClosure\SuperClosure::getBinding
     * @covers \SuperClosure\SuperClosure::__invoke
     */
    public function testBasicAccessorBehavior()
    {
        $closure = function () {return 'foo';};
        $superClosure = new SuperClosure($closure);

        $this->assertSame($closure, $superClosure->getClosure());
        $this->assertInstanceOf('ReflectionFunction', $superClosure->getReflection());
        $this->assertInstanceOf('SuperClosure\ClosureBinding', $superClosure->getBinding());
        $this->assertEquals($closure(), $superClosure());
    }

    /**
     * @covers \SuperClosure\SuperClosure::bindTo
     */
    public function testBindToThrowsExceptionOnOldPhpVersions()
    {
        if (PHP_VERSION_ID >= 50400) {
            $this->markTestSkipped('This test requires a PHP version less than 5.4.');
        }

        $this->setExpectedException('RuntimeException');

        $sc = new SuperClosure(function(){});
        $sc->bindTo($this);
    }

    /**
     * @covers \SuperClosure\SuperClosure::bindTo
     */
    public function testBindToBehavior()
    {
        if (PHP_VERSION_ID < 50400) {
            $this->markTestSkipped('This test requires PHP version 5.4 or later.');
        }

        $sc = new SuperClosure(function(){});
        $originalClosure = $sc->getBinding();
        $binding = $this->getMockClosureBinding();
        $bindResult = $sc->bindTo($binding);
        $closureAfterBinding = $sc->getClosure();

        $this->assertSame($sc, $bindResult);
        $this->assertNotSame($originalClosure, $closureAfterBinding);
    }
}
