<?php

namespace SuperClosure\Test\Unit\ClosureParser\Ast;

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
        /** @var \PHPParser_Node_Expr_Closure $ast */
        $ast = $this->getMockParserNode('PHPParser_Node_Expr_Closure');

        $context = new AstClosureContext(
            '',
            array(),
            $ast,
            new ClosureLocation,
            $this->getMockClosureBinding()
        );

        $this->assertInstanceOf('PHPParser_Node_Expr_Closure', $context->getAst());
        $this->assertInstanceOf('SuperClosure\ClosureParser\Ast\ClosureLocation', $context->getLocation());
    }
}
