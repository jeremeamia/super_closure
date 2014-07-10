<?php namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\ClosureParser;
use SuperClosure\SuperClosure;
use SuperClosure\Test\Unit\UnitTestBase;

class ConcreteClosureParser extends ClosureParser
{
    public function parse($closure)
    {
        return $this->prepareClosure($closure);
    }
}

/**
 * @covers \SuperClosure\ClosureParser\ClosureParser
 */
class ClosureParserTest extends UnitTestBase
{
    public function testParserHandlesClosureTypes()
    {
        $parser = new ConcreteClosureParser();

        $closure = function () {};
        $result = $parser->parse($closure);
        $this->assertInstanceOf('SuperClosure\\SuperClosure', $result);

        $closure = new SuperClosure(function () {});
        $result = $parser->parse($closure);
        $this->assertInstanceOf('SuperClosure\\SuperClosure', $result);

        $this->setExpectedException('InvalidArgumentException');
        $closure = 5;
        $parser->parse($closure);
    }

    /**
     * @covers \SuperClosure\ClosureParser\ClosureParser::create
     */
    public function testCanCreateParser()
    {
        $parser1 = ClosureParser::create();
        $this->assertInstanceOf('SuperClosure\ClosureParser\Ast\AstParser', $parser1);

        $parser2 = ClosureParser::create(array(
            ClosureParser::HANDLE_MAGIC_CONSTANTS  => false,
            ClosureParser::HANDLE_CLASS_NAMES      => false,
        ));
        $this->assertInstanceOf('SuperClosure\ClosureParser\Token\TokenParser', $parser2);
    }
}
