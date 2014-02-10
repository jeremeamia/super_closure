<?php

namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\Options;
use SuperClosure\SuperClosure;
use SuperClosure\Test\Unit\UnitTestBase;

/**
 * @covers \SuperClosure\ClosureParser\AbstractClosureParser
 */
class AbstractClosureParserTest extends UnitTestBase
{
    public function testParserHandlesClosureTypesAndOptions()
    {
        $parser = new ConcreteClosureParser(new Options);

        $closure = function () {};
        $result = $parser->parse($closure);
        $this->assertInstanceOf('SuperClosure\\SuperClosure', $result);

        $closure = new SuperClosure(function () {});
        $result = $parser->parse($closure);
        $this->assertInstanceOf('SuperClosure\\SuperClosure', $result);

        $this->setExpectedException('InvalidArgumentException');
        $closure = 5;
        $result = $parser->parse($closure);
    }
}
