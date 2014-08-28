<?php

namespace Jeremeamia\SuperClosure\Test\Visitor;

use Jeremeamia\SuperClosure\Visitor\ThisVariableDetectorVisitor;

/**
 * @covers Jeremeamia\SuperClosure\Visitor\ThisVariableDetectorVisitor
 */
final class ThisVariableDetectorVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testPositiveAndNegativeCases()
    {
        $detectorVisitor = new ThisVariableDetectorVisitor();
        $this->assertFalse($detectorVisitor->wasDetected());

        $detectorVisitor->leaveNode(new \PHPParser_Node_Expr_Variable('foo'));
        $this->assertFalse($detectorVisitor->wasDetected());

        $detectorVisitor->leaveNode(new \PHPParser_Node_Expr_Variable('this'));
        $this->assertTrue($detectorVisitor->wasDetected());
    }
}
