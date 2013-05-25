<?php

namespace Jeremeamia\SuperClosure\Test;

use Jeremeamia\SuperClosure\SerializableClosure;

class SerializableClosureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializableClosure
     */
    public $serializableClosure;

    /**
     * @var \Closure
     */
    public $originalClosure;

    public function setup()
    {
        $base = 2;
        $exp = function ($power) use ($base) {
            return (int) pow($base, $power);
        };

        $this->originalClosure = $exp;
        $this->serializableClosure = new SerializableClosure($exp);
    }

    public function testGetClosureReturnsTheOriginalClosure()
    {
        $this->assertInstanceOf('\Closure', $this->serializableClosure->getClosure());
        $this->assertSame($this->originalClosure, $this->serializableClosure->getClosure());
    }

    public function testInvokeActuallyInvokesTheOriginalClosure()
    {
        $originalClosure = $this->originalClosure;
        $serializableClosure = $this->serializableClosure;

        $originalClosureResult = $originalClosure(4);
        $serializableClosureResult = $serializableClosure(4);

        $this->assertEquals($originalClosureResult, $serializableClosureResult);
    }
}
