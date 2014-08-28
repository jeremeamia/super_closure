<?php

namespace Jeremeamia\SuperClosure\Test\Visitor;

use Jeremeamia\SuperClosure\Visitor\ScalarValuesVisitor;

/**
 * @covers Jeremeamia\SuperClosure\Visitor\ScalarValuesVisitor
 */
final class ScalarValuesVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testRemovingUseStatement()
    {
        $usedVars = array(
            'a' => 42,
            'b' => 3.14,
            'c' => false,
            'd' => null,
            'e' => array('hey')
        );

        $detectorVisitor = new ScalarValuesVisitor($usedVars);

        /** @var \PHPParser_Node_Expr_Closure[] $nodes */
        $nodes = $detectorVisitor->beforeTraverse(
            array(
                new \PHPParser_Node_Expr_Closure(
                    array(
                        'uses' => array(
                            new \PHPParser_Node_Expr_ClosureUse('a'),
                            new \PHPParser_Node_Expr_ClosureUse('b'),
                            new \PHPParser_Node_Expr_ClosureUse('c'),
                            new \PHPParser_Node_Expr_ClosureUse('d'),
                            new \PHPParser_Node_Expr_ClosureUse('e')
                        )
                    )
                )
            )
        );

        $expectedAst = new \PHPParser_Node_Expr_Closure(
            array(
                'stmts' => array(
                    new \PHPParser_Node_Expr_Assign(
                        new \PHPParser_Node_Expr_Variable('e'),
                        new \PHPParser_Node_Expr_Array(array(new \PHPParser_Node_Scalar_String('hey')))
                    ),
                    new \PHPParser_Node_Expr_Assign(
                        new \PHPParser_Node_Expr_Variable('d'),
                        new \PHPParser_Node_Expr_ConstFetch(
                            new \PHPParser_Node_Name_FullyQualified('null')
                        )
                    ),
                    new \PHPParser_Node_Expr_Assign(
                        new \PHPParser_Node_Expr_Variable('c'),
                        new \PHPParser_Node_Expr_ConstFetch(
                            new \PHPParser_Node_Name_FullyQualified('false')
                        )
                    ),
                    new \PHPParser_Node_Expr_Assign(
                        new \PHPParser_Node_Expr_Variable('b'),
                        new \PHPParser_Node_Scalar_DNumber(3.14)
                    ),
                    new \PHPParser_Node_Expr_Assign(
                        new \PHPParser_Node_Expr_Variable('a'),
                        new \PHPParser_Node_Scalar_LNumber(42)
                    ),
                )
            )
        );

        $this->assertEquals($expectedAst, $nodes[0]);
    }
}
