<?php namespace SuperClosure\Test\Integ;

use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Analyzer\TokenAnalyzer;
use SuperClosure\SerializableClosure;
use SuperClosure\Serializer;
use SuperClosure\Test\Integ\Fixture\Collection;
use SuperClosure\Test\Integ\Fixture\Foo;

class SerializationTest extends \PHPUnit_Framework_TestCase
{
    public function testSerializeBasicClosure()
    {
        $closure = function ($a, $b) {
            return $a + $b;
        };

        $results = $this->getResults($closure, [4, 7]);
        $this->assertAllEquals(11, $results);
    }

    public function testSerializeBasicClosureViaClosureWrapping()
    {
        $closure = function ($a, $b) {
            return $a + $b;
        };
        $serializableClosure = new SerializableClosure($closure);
        $results = [];
        $results[] = $serializableClosure(1, 2);
        $serialized = serialize($serializableClosure);
        $unserialized = unserialize($serialized);
        /** @var callable $unserialized */
        $results[] = $unserialized(1, 2);

        $this->assertAllEquals(3, $results);
    }

    public function testSerializeClosureWithUseStatement()
    {
        $operand = 8;
        $closure = function ($num) use ($operand) {
            return $num + $operand;
        };

        $results = $this->getResults($closure, [7]);
        $this->assertAllEquals(15, $results);
    }

    public function testSerializeClosureThatUsesAnotherClosure()
    {
        $otherClosure = function ($n) {return $n + 2;};
        $closure = function ($n) use ($otherClosure) {
            return $otherClosure($n + 5);
        };

        $results = $this->getResults($closure, [8]);
        $this->assertAllEquals(15, $results);
    }

    public function testSerializeAOneLineClosure()
    {
        $c = $d = 5;
        $closure = function($a,$b)use($c,$d){return$a+$b+$c+$d;};

        $results = $this->getResults($closure, [2, 8]);
        $this->assertAllEquals(20, $results);
    }

    public function testSerializeClosureAndPreserveMagicConstants()
    {
        $closure = function () {
            return basename(__FILE__);
        };

        $results = $this->getResults($closure);
        $this->assertEquals('SerializationTest.php', $results['original']);
        $this->assertEquals('SerializationTest.php', $results['ast']);
        // Doesn't work with the TokenAnalyzer.
        $this->assertStringEndsWith('eval()\'d code', $results['token']);
    }

    public function testSerializeClosureAndMakeClassNamesFullyQualified()
    {
        $closure = function (Collection $collection) {
            return iterator_to_array($collection);
        };

        $results = $this->getResults($closure, [new Collection(['foo', 'bar'])]);
        $this->assertEquals(['foo', 'bar'], $results['original']);
        $this->assertEquals(['foo', 'bar'], $results['ast']);
        // Doesn't work with the TokenAnalyzer.
        $this->assertStringEndsWith('ERROR', $results['token']);
    }

    public function testCannotSerializeClosureWhenOneTheSameLineAsAnother()
    {
        $closure = function($a){return$a;};function($b){return$b;};

        $results = $this->getResults($closure, [5]);
        $this->assertEquals(5, $results['original']);
        $this->assertEquals('ERROR', $results['ast']);
        $this->assertEquals('ERROR', $results['token']);
    }

    public function testSerializeClosureWithComposition()
    {
        $inc = function ($n) {return $n + 1;};
        $dec = function ($n) {return $n - 1;};
        $compose = function ($f1, $f2) {
            return function ($n) use ($f1, $f2) {
                return $f2($f1($n));
            };
        };
        $closure = $compose($compose($compose($inc, $inc), $dec), $inc);

        $results = $this->getResults($closure, [2]);
        $this->assertAllEquals(4, $results);
    }

    public function testSerializeClosureWithBinding()
    {
        $foo = new Foo(10);
        $closure = $foo->getClosure();

        $results = $this->getResults($closure, []);
        $this->assertAllEquals(10, $results);
    }

    public function testSerializeRecursiveClosure()
    {
        $factorial = function ($num) use (&$factorial) {
            return $num <= 1 ? 1 : $num * $factorial($num - 1);
        };

        $results = $this->getResults($factorial, [5]);
        $this->assertAllEquals(120, $results);
    }

    public function testSerializeStaticClosure()
    {
        $closure = static function () {
            return 10;
        };

        $results = $this->getResults($closure, []);
        $this->assertAllEquals(10, $results);
    }

    public function testClosuresInContextAreUnboxedBackToClosures()
    {
        $usedFn = function () {};
        $closure = function () use ($usedFn){
            return get_class($usedFn);
        };

        $results = $this->getResults($closure, []);
        $this->assertAllEquals('Closure', $results);
    }

    private function getResults(\Closure $closure, array $args = [])
    {
        $results = ['original' => call_user_func_array($closure, $args)];

        // AST
        try {
            $serializer = new Serializer(new AstAnalyzer, 'hashkey');
            $serialized = $serializer->serialize($closure);
            $unserialized = $serializer->unserialize($serialized);
            $results['ast'] = call_user_func_array($unserialized, $args);
        } catch (\Exception $e) {
            $results['ast'] = 'ERROR';
        } catch (\Throwable $e) {
            $results['ast'] = 'ERROR';
        }

        // Token
        try {
            $serializer = new Serializer(new TokenAnalyzer, 'hashkey');
            $serialized = $serializer->serialize($closure);
            $unserialized = $serializer->unserialize($serialized);
            $results['token'] = call_user_func_array($unserialized, $args);
        } catch (\Exception $e) {
            $results['token'] = 'ERROR';
        } catch (\Throwable $e) {
            $results['token'] = 'ERROR';
        }

        return $results;
    }

    private function assertAllEquals($expected, array $actuals)
    {
        foreach ($actuals as $actual) {
            $this->assertEquals($expected, $actual);
        }
    }
}
