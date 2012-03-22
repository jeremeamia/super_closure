<?php

namespace SuperClosure\Test;

use SuperClosure\SuperClosure;

class SuperClosureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \SuperClosure\SuperClosure The SuperClosure instance
     */
    public $superClosure;

    /**
     * @var \Closure The original closure
     */
    public $originalClosure;

    public function setup()
    {
        $base           = 2;
        $exponentialize = function($power) use($base) {
            return (integer) pow($base, $power);
        };

        $this->superClosure    = new SuperClosure($exponentialize);
        $this->originalClosure = $exponentialize;
    }

    /**
     * @covers SuperClosure\SuperClosure::getClosure
     * @covers SuperClosure\SuperClosure::__construct
     */
    public function testGetClosureReturnsTheOriginalClosure()
    {
        $this->assertInstanceOf('\Closure', $this->superClosure->getClosure());
    }

    /**
     * @covers SuperClosure\SuperClosure::__invoke
     */
    public function testInvokeActuallyInvokesTheOriginalClosure()
    {
        $original_closure = $this->originalClosure;
        $super_closure    = $this->superClosure;

        $original_closure_result = $original_closure(4);
        $super_closure_result    = $super_closure(4);

        $this->assertEquals($original_closure_result, $super_closure_result);
    }

    /**
     * @covers SuperClosure\SuperClosure::serialize
     * @covers SuperClosure\SuperClosure::unserialize
     */
    public function testSerializingAndUnserializingDoesNotAlterSuperClosure()
    {
        $original_code = $this->superClosure->getCode();
        $serialized    = serialize($this->superClosure);
        $unserialized  = unserialize($serialized);
        $this->assertEquals($original_code, $unserialized->getCode());
    }
}
