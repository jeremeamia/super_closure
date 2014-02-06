<?php

namespace SuperClosure\Test\Unit;

use SuperClosure\ClosureBinding;

/**
 * Class ClosureBindingTest
 */
class ClosureBindingTest extends UnitTestBase
{
    /**
     * @covers \SuperClosure\ClosureBinding::__construct
     * @covers \SuperClosure\ClosureBinding::getObject
     * @covers \SuperClosure\ClosureBinding::getScope
     */
    public function testBasicAccessorBehavior()
    {
        $object = new \SplQueue;
        $class = 'SplQueue';
        $binding = new ClosureBinding($object, $class);

        $this->assertSame($object, $binding->getObject());
        $this->assertEquals($class, $binding->getScope());
    }

    /**
     * @covers \SuperClosure\ClosureBinding::fromClosure
     * @covers \SuperClosure\ClosureBinding::fromReflection
     */
    public function testBindToThrowsExceptionOnOldPhpVersions()
    {
        $closure = function () {};
        $binding = ClosureBinding::fromClosure($closure);

        if (PHP_VERSION_ID >= 50400) {
            $this->assertSame($this, $binding->getObject());
            $this->assertSame(get_class($this), $binding->getScope());
        } else {
            $this->assertNull($binding->getObject());
            $this->assertNull($binding->getScope());
        }
    }

    /**
     * @covers \SuperClosure\ClosureBinding::fromReflection
     */
    public function testThrowsExceptionIfReflectionIsNotOfAClosure()
    {
        $this->setExpectedException('InvalidArgumentException');

        $reflection = new \ReflectionFunction('substr');
        $binding = ClosureBinding::fromReflection($reflection);
    }
}
