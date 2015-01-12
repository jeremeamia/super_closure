<?php namespace SuperClosure\Test\Integ;

use SuperClosure\Serializer;

class SerializationTest extends \PHPUnit_Framework_TestCase
{
    private function getBeforeAndAfter(
        $analyzer,
        \Closure $closure,
        array $args,
        $includeBinding = false
    ) {
        $analyzer = 'SuperClosure\\Analyzer\\' . ucwords($analyzer) . 'Analyzer';
        $serializer = new Serializer([
            Serializer::OPT_ANALYZER    => new $analyzer,
            Serializer::OPT_INC_BINDING => $includeBinding
        ]);

        $before = call_user_func_array($closure, $args);
        $serialized = $serializer->serialize($closure);
        $unserialized = $serializer->unserialize($serialized);
        $after = call_user_func_array($unserialized, $args);

        return [$before, $after];
    }

    public function testBasicClosure()
    {
        $c = function ($a, $b) {
            return $a + $b;
        };

        list($b, $a) = $this->getBeforeAndAfter('ast', $c, [4, 7]);
        $this->assertEquals(11, $b);
        $this->assertEquals(11, $a);
        list($b, $a) = $this->getBeforeAndAfter('token', $c, [4, 7]);
        $this->assertEquals(11, $b);
        $this->assertEquals(11, $a);
    }

    public function testClosureWithUseStatement()
    {
        $operand = 8;
        $c = function ($num) use ($operand) {
            return $num + $operand;
        };

        list($b, $a) = $this->getBeforeAndAfter('ast', $c, [7]);
        $this->assertEquals(15, $b);
        $this->assertEquals(15, $a);
        list($b, $a) = $this->getBeforeAndAfter('token', $c, [7]);
        $this->assertEquals(15, $b);
        $this->assertEquals(15, $a);
    }
}
