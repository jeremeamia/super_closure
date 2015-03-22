<?php namespace SuperClosure\Test\Unit\Analyzer\Visitor;

use SuperClosure\Analyzer\Visitor\ThisDetectorVisitor;

/**
 * @covers SuperClosure\Analyzer\Visitor\ThisDetectorVisitor
 */
class ThisDetectorVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testThisIsDiscovered()
    {
        $visitor = new ThisDetectorVisitor();

        $visitor->leaveNode(new \PhpParser\Node\Expr\Variable('this'));

        $this->assertTrue($visitor->detected);
    }

    public function testThisIsNotDiscovered()
    {
        $visitor = new ThisDetectorVisitor();

        $visitor->leaveNode(new \PhpParser\Node\Expr\Variable('foo'));

        $this->assertFalse($visitor->detected);
    }

    public function testThisIsNotDiscoveredWithNonVariable()
    {
        $visitor = new ThisDetectorVisitor();

        $visitor->leaveNode(new \PhpParser\Node\Expr\Closure());

        $this->assertFalse($visitor->detected);
    }
}
