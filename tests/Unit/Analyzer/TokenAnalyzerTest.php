<?php namespace SuperClosure\Test\Unit\Analyzer;

use SuperClosure\Serializer;
use SuperClosure\Analyzer\TokenAnalyzer;

class TokenAnalyzerTest extends \PHPUnit_Framework_TestCase
{
    public function testDollarOpenCurlyBraces()
    {
        $greeting = 'hello';
        $hello = function ($name = 'world') use ($greeting) {
            return "{$greeting}, {$name}";
        };
        $unserialized = $this->prepareOpenCurlyBracesTest($hello);

        $this->assertEquals('hello, world', $unserialized());
        $this->assertEquals('hello, user', $unserialized('user'));
    }

    public function testAnotherStyleDollarOpenCurlyBraces()
    {
        $greeting = 'hello';
        $hello = function ($name = 'world') use ($greeting) {
            return "${greeting}, ${name}";
        };
        $unserialized = $this->prepareOpenCurlyBracesTest($hello);

        $this->assertEquals('hello, world', $unserialized());
        $this->assertEquals('hello, user', $unserialized('user'));
    }

    public function testDollarOpenCurlyBracesWithFunctions()
    {
        $greeting = 'hello';
        $hello = function ($name = 'world') use ($greeting) {
            return "{$greeting}, {${$this->getOpenCurlyBracesTestArgument()}}";
        };
        $unserialized = $this->prepareOpenCurlyBracesTest($hello);

        $this->assertEquals('hello, world', $unserialized());
    }

    public function testSimplerDollarOpenCurlyBracesWithFunctions()
    {
        $greeting = 'hello';
        $hello = function ($name = 'world') use ($greeting) {
            return "${greeting}, ${$this->getOpenCurlyBracesTestArgument()}";
        };
        $unserialized = $this->prepareOpenCurlyBracesTest($hello);

        $this->assertEquals('hello, world', $unserialized());
    }

    private function getOpenCurlyBracesTestArgument()
    {
        return 'name';
    }

    private function prepareOpenCurlyBracesTest(\Closure $closure)
    {
        $serializer = new Serializer(new TokenAnalyzer());
        $serialized = $serializer->serialize($closure);

        return $serializer->unserialize($serialized);
    }
}
