<?php

namespace Jeremeamia\SuperClosure\Test;

use Jeremeamia\SuperClosure\ClosureFinderVisitor;

/**
 * @covers Jeremeamia\SuperClosure\ClosureFinderVisitor
 */
class ClosureFinderVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testClosureNodeIsDiscoveredByVisitor()
    {
        $closure = function(){}; // Take the line number here and set it as the "startLine"
        $reflectedClosure = new \ReflectionFunction($closure);
        $closureFinder = new ClosureFinderVisitor($reflectedClosure);
        $closureNode = new \PHPParser_Node_Expr_Closure(array(), array('startLine' => 14));
        $closureFinder->leaveNode($closureNode);

        $this->assertSame($closureNode, $closureFinder->getClosureNode());
    }

    public function testClosureNodeIsAmbiguousIfMultipleClosuresOnLine()
    {
        $this->setExpectedException('RuntimeException');

        $closure = function(){}; function(){}; // Take the line number here and set it as the "startLine"
        $closureFinder = new ClosureFinderVisitor(new \ReflectionFunction($closure));
        $closureFinder->leaveNode(new \PHPParser_Node_Expr_Closure(array(), array('startLine' => 27)));
        $closureFinder->leaveNode(new \PHPParser_Node_Expr_Closure(array(), array('startLine' => 27)));
    }
}
