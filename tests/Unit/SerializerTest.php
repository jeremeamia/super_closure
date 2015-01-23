<?php namespace SuperClosure\Test\Unit;

use SuperClosure\Analyzer\ClosureAnalyzer;
use SuperClosure\Analyzer\TokenAnalyzer;
use SuperClosure\Serializer;

/**
 * @covers \SuperClosure\Serializer
 */
class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializingAndUnserializing()
    {
        $serializer = new Serializer(new TokenAnalyzer());
        $originalFn = function ($n) {return $n +  5;};
        $serializedFn = $serializer->serialize($originalFn);
        $unserializedFn = $serializer->unserialize($serializedFn);

        $this->assertEquals(10, $originalFn(5));
        $this->assertEquals(10, $unserializedFn(5));
    }

    public function testGettingClosureData()
    {
        $adjustment = 2;
        $fn = function ($n) use (&$fn, $adjustment) {
            $result = $n > 1 ? $n * $fn($n - 1) : 1;
            return $result + $adjustment;
        };

        $serializer = new Serializer(new TokenAnalyzer());

        // Test getting full closure data.
        $data = $serializer->getData($fn);
        $this->assertCount(8, $data);
        $this->assertInstanceOf('ReflectionFunction', $data['reflection']);
        $this->assertGreaterThan(0, strpos($data['code'], '$adjustment'));
        $this->assertFalse($data['hasThis']);
        $this->assertCount(2, $data['context']);
        $this->assertTrue($data['hasRefs']);
        $this->assertInstanceOf(__CLASS__, $data['binding']);
        $this->assertEquals(__CLASS__, $data['scope']);
        $this->assertInternalType('array', $data['tokens']);

        // Test getting serializable closure data.
        $data = $serializer->getData($fn, true);
        $this->assertCount(5, $data);
        $this->assertTrue(in_array(Serializer::RECURSION, $data['context']));
        $this->assertNull($data['binding']);
        $this->assertEquals(__CLASS__, $data['scope']);
        $this->assertArrayNotHasKey('tokens', $data);
        $this->assertArrayHasKey('serializer', $data);
    }

    /**
     * @return ClosureAnalyzer
     */
    private function getMockAnalyzer(array $data)
    {
        $analyzer = $this->getMockBuilder('SuperClosure\Analyzer\ClosureAnalyzer')
            ->setMethods(['analyze'])
            ->getMockForAbstractClass();

        $analyzer->method('analyze')->willReturn($data);

        return $analyzer;
    }
}
