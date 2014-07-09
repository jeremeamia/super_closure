<?php

namespace SuperClosure\Test\Integration;

use SuperClosure\ClosureParser\ClosureParser;
use SuperClosure\ClosureParser\Token\TokenParser as TokenParser;
use SuperClosure\ClosureParser\Ast\AstParser as AstParser;
use SuperClosure\SerializableClosure;

class ClosureSerializationTest extends \PHPUnit_Framework_TestCase
{
    public function provideTestCases()
    {
        $tokenParser = new TokenParser;
        $astParser = new AstParser;
        $testCases = array();
        $addTestCase = function (
            $closure,
            array $args,
            ClosureParser $parser,
            $result,
            $matches = true
        ) use (&$testCases) {
            if (!$closure instanceof SerializableClosure) {
                $closure = new SerializableClosure($closure, $parser);
            }
            $testCases[] = array($closure, $args, $result, $matches);
        };

        include('test-cases-classes.php');

        if (PHP_VERSION_ID > 50300) {
            include(__DIR__ . '/test-cases-php53.php');
            echo "Loaded PHP 5.3+ SuperClosure integration tests.\n";
        }

        if (PHP_VERSION_ID > 50400) {
            include(__DIR__ . '/test-cases-php54.php');
            echo "Loaded PHP 5.4+ SuperClosure integration tests.\n";
        }

        return $testCases;
    }

    /**
     * @dataProvider provideTestCases
     */
    public function testClosureSerialization(
        SerializableClosure $closure,
        array $args,
        $expectedResult,
        $matchesOriginalResult
    ) {
        if ($expectedResult instanceof \Exception) {
            $this->setExpectedException('Exception');
        }

        $originalResult = call_user_func_array($closure, $args);
        $serializedClosure = serialize($closure);
        $unserializedClosure = unserialize($serializedClosure);
        $actualResult = call_user_func_array($unserializedClosure, $args);

        $this->assertSame($expectedResult, $actualResult);
        if ($matchesOriginalResult) {
            $this->assertSame($originalResult, $actualResult);
        } else {
            $this->assertNotSame($originalResult, $actualResult);
        }
    }
}
