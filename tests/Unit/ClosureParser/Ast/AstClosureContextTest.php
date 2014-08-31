<?php namespace SuperClosure\Test\Unit\ClosureParser\Ast;

use SuperClosure\ClosureParser\Ast\AstClosureContext;
use SuperClosure\ClosureParser\Ast\ClosureLocation;
use SuperClosure\Test\Unit\UnitTestBase;

/**
 * @covers \SuperClosure\ClosureParser\Ast\AstClosureContext
 */
class AstClosureContextTest extends UnitTestBase
{
    public function testCanGetDataFromContext()
    {
        /** @var \PhpParser\Node\Expr\Closure $ast */
        $ast = $this->getMockParserNode('PhpParser\Node\Expr\Closure');

        $context = new AstClosureContext(
            '',
            array(),
            $ast,
            new ClosureLocation,
            $this->getMockClosureBinding()
        );

        $this->assertInstanceOf('PhpParser\Node\Expr\Closure', $context->getAst());
        $this->assertInstanceOf('SuperClosure\ClosureParser\Ast\ClosureLocation', $context->getLocation());
    }
}
