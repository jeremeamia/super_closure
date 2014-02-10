<?php

namespace SuperClosure\Test\Unit;

use SuperClosure\SerializableClosure;

/**
 * Class SerializableClosureTest
 */
class SerializableClosureTest extends UnitTestBase
{
    /**
     * @covers \SuperClosure\SerializableClosure::__construct
     * @covers \SuperClosure\SerializableClosure::getCode
     * @covers \SuperClosure\SerializableClosure::getVariables
     */
    public function testBasicAccessorBehavior()
    {
        $closure = function () {};
        $parser = $this->getMockClosureParser($this->getMockClosureContext('CODE', array('VARIABLES')));
        $sc = new SerializableClosure($closure, $parser);

        $this->assertSame('CODE', $sc->getCode());
        $this->assertSame(array('VARIABLES'), $sc->getVariables());
    }

    /**
     * @covers \SuperClosure\SerializableClosure::serialize
     * @covers \SuperClosure\SerializableClosure::fetchSerializableData
     */
    public function testBasicSerialization()
    {
        $closure = function () {};
        $parser = $this->getMockClosureParser();
        $sc = new SerializableClosure($closure, $parser);
        $serialization = serialize($sc);

        $this->assertSame(self::DUMMY_SERIALIZED_CLOSURE, $serialization);
    }

    /**
     * @covers \SuperClosure\SerializableClosure::serialize
     * @covers \SuperClosure\SerializableClosure::fetchSerializableData
     */
    public function testSerializationWithInnerClosures()
    {
        $foo = 'foo';
        $bar = function () {};
        $closure = function () use ($foo, $bar) {};
        $sc = new SerializableClosure($closure);
        $serialization = serialize($sc);

        $this->assertStringStartsWith('C:32:"SuperClosure\\SerializableClosure"', $serialization);
    }

    /**
     * @covers \SuperClosure\SerializableClosure::serialize
     * @covers \SuperClosure\SerializableClosure::fetchSerializableData
     */
    public function testSerializationIsNullOnParsingErrors()
    {
        $closure = function () {};function () {};
        $sc = new SerializableClosure($closure);
        $serialization = serialize($sc);

        $this->assertEquals('N;', $serialization);
    }

    /**
     * @covers \SuperClosure\SerializableClosure::unserialize
     */
    public function testBasicUnserialization()
    {
        $sc = unserialize(self::SIMPLE_SERIALIZED_CLOSURE);

        $this->assertInstanceOf('SuperClosure\SuperClosure', $sc);
    }

    /**
     * @covers \SuperClosure\SerializableClosure::unserialize
     */
    public function testUnserializationThrowsExceptionOnBadCode()
    {
        $this->setExpectedException('SuperClosure\ClosureUnserializationException');

        unserialize(self::DUMMY_SERIALIZED_CLOSURE);
    }
}
