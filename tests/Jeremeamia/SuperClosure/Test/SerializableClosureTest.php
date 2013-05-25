<?php

namespace Jeremeamia\SuperClosure\Test;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * @covers \Jeremeamia\SuperClosure\SerializableClosure
 */
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

    public function testClosureInvokesTheOriginalClosure()
    {
        $this->assertInstanceOf('\Closure', $this->serializableClosure->getClosure());
        $this->assertSame($this->originalClosure, $this->serializableClosure->getClosure());
        $this->assertEquals(
            call_user_func($this->originalClosure, 4),
            call_user_func($this->serializableClosure, 4)
        );
    }

    public function testClosureBehavesTheSameAfterSerializationProcess()
    {
        $originalReturnValue = call_user_func($this->serializableClosure, 4);
        $serializedClosure = serialize($this->serializableClosure);
        $unserializedClosure = unserialize($serializedClosure);
        $finalReturnValue = call_user_func($unserializedClosure, 4);

        $this->assertEquals($originalReturnValue, $finalReturnValue);
    }
}
