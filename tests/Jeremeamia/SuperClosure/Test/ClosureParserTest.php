<?php

namespace Jeremeamia\SuperClosure\Test;

use Jeremeamia\SuperClosure\ClosureParser;

/**
 * @covers \Jeremeamia\SuperClosure\ClosureParser
 */
class ClosureParserTest extends \PHPUnit_Framework_TestCase
{
    public function testCanGetReflectionBackFromParser()
    {
        $closure = function () {};
        $reflection = new \ReflectionFunction($closure);
        $parser = new ClosureParser($reflection);

        $this->assertSame($reflection, $parser->getReflection());
    }

    public function testCanUseFactoryMethodToCreateParser()
    {
        $parser = ClosureParser::fromClosure(function () {});

        $this->assertInstanceOf('Jeremeamia\SuperClosure\ClosureParser', $parser);
    }

    public function testRaisesErrorWhenNonClosureIsProvided()
    {
        $this->setExpectedException('InvalidArgumentException');

        $reflection = new \ReflectionFunction('strpos');
        $parser = new ClosureParser($reflection);
    }
}
