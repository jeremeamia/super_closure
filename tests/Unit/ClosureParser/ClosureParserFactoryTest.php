<?php

namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\ClosureParserFactory;
use SuperClosure\ClosureParser\Options;
use SuperClosure\Test\Unit\UnitTestBase;

class ClosureParserFactoryTest extends UnitTestBase
{

    /**
     * @covers \SuperClosure\ClosureParser\ClosureParserFactory::create
     */
    public function testCanCreateParser()
    {
        $factory = new ClosureParserFactory;

        $parser1 = $factory->create();
        $this->assertInstanceOf('SuperClosure\ClosureParser\Ast\AstParser', $parser1);

        $parser2 = $factory->create(array(
            Options::HANDLE_MAGIC_CONSTANTS  => false,
            Options::HANDLE_CLASS_NAMES      => false,
        ));
        $this->assertInstanceOf('SuperClosure\ClosureParser\Token\TokenParser', $parser2);
    }

    /**
     * @covers \SuperClosure\ClosureParser\ClosureParserFactory::setDefaultOptions
     */
    public function testCanChangeDefaultOptions()
    {
        $factory = new ClosureParserFactory;

        $parser1 = $factory->create();
        $this->assertInstanceOf('SuperClosure\ClosureParser\Ast\AstParser', $parser1);

        $factory->setDefaultOptions(array(
            Options::HANDLE_CLOSURE_BINDINGS => true,
            Options::HANDLE_MAGIC_CONSTANTS  => false,
            Options::HANDLE_CLASS_NAMES      => false,
            Options::VALIDATE_TOKENS         => true,
        ));
        $parser2 = $factory->create();
        $this->assertInstanceOf('SuperClosure\ClosureParser\Token\TokenParser', $parser2);
    }
}
