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
            $serializer->getClosureData($closure)
        );
    }

    /**
     * @param bool $error
     *
     * @return Serializer
     */
    private function getMockSerializer($error = false)
    {
        $serializer = $this->getMockBuilder('SuperClosure\Serializer')
            ->setMethods(['getClosureData'])
            ->getMock();

        if ($error) {
            $serializer->method('getClosureData')->willThrowException(
                new ClosureAnalysisException
            );
        } else {
            $serializer->method('getClosureData')->willReturn([
                'code'       => 'function () {};',
                'context'    => [],
                'binding'    => null,
                'scope'      => 'static',
            ]);
        }

        return $serializer;
    }
}
