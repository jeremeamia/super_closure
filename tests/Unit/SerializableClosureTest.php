<?php namespace SuperClosure\Test\Unit;

use SuperClosure\Exception\ClosureAnalysisException;
use SuperClosure\SerializableClosure;
use SuperClosure\Serializer;

/**
 * @covers \SuperClosure\SerializableClosure
 */
class SerializableClosureTest extends \PHPUnit_Framework_TestCase
{
    public function testGanGetAndInvokeTheClosure()
    {
        $closure = function () {return 4;};
        $sc = new SerializableClosure($closure, $this->getMockSerializer());
        $this->assertSame($closure, $sc->getClosure());
        $this->assertEquals(4, $sc());
    }

    public function testCanSerializeAClosure()
    {
        $closure = function () {};
        $sc = new SerializableClosure($closure, $this->getMockSerializer());
        $serialization = serialize($sc);

        $this->assertGreaterThan(0, strpos($serialization, 'function () {};'));
    }

    public function testSerializationTriggersNoticeOnBadClosure()
    {
        $formerLevel = error_reporting(-1);
        $closure = function () {};function () {};
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        $sc = new SerializableClosure($closure, $this->getMockSerializer(true));
        $serialization = serialize($sc);
        error_reporting($formerLevel);
    }

    public function testSerializationReturnsNullOnBadClosure()
    {
        $formerLevel = error_reporting(0);
        $closure = function () {};function () {};
        $sc = new SerializableClosure($closure, $this->getMockSerializer(true));
        $serialization = serialize($sc);
        $this->assertEquals('N;', $serialization);
        error_reporting($formerLevel);
    }

    public function testDebuggingCallsSerializer()
    {
        $closure = function () {};
        $serializer = $this->getMockSerializer();
        $sc = new SerializableClosure($closure, $serializer);
        $this->assertEquals(
            $sc->__debugInfo(),
            $serializer->getData($closure)
        );
    }

    public function testUnserializationFailsIfClosureCorrupt()
    {
        $serialized = file_get_contents(__DIR__ . '/serialized-corrupt.txt');
        $this->setExpectedException('SuperClosure\Exception\ClosureUnserializationException');
        unserialize($serialized);
    }

    public function testUnserializationWorksForRecursiveClosures()
    {
        $serialized = file_get_contents(__DIR__ . '/serialized-recursive.txt');
        /** @var \Closure $unserialized */
        $unserialized = unserialize($serialized);
        $this->assertEquals(120, $unserialized(5));
    }

    public function testCanSerializeAndUnserializeMultipleTimes()
    {
        $closure = function () {};
        $serializer = $this->getMockSerializer();
        $sc = new SerializableClosure($closure, $serializer);
        $s1 = serialize($sc);
        $u1 = unserialize($s1);
        $s2 = serialize($u1);
        $this->assertEquals($s1, $s2);
    }

    /**
     * @param bool $error
     *
     * @return Serializer
     */
    private function getMockSerializer($error = false)
    {
        $serializer = $this->getMockBuilder('SuperClosure\Serializer')
            ->setMethods(['getData'])
            ->getMock();

        if ($error) {
            $serializer->method('getData')->willThrowException(
                new ClosureAnalysisException
            );
        } else {
            $serializer->method('getData')->willReturn([
                'code'       => 'function () {};',
                'context'    => [],
                'binding'    => null,
                'scope'      => 'static',
            ]);
        }

        return $serializer;
    }
}
