<?php namespace SuperClosure\Test\Unit\Analyzer\Visitor;

use SuperClosure\Analyzer\Visitor\ClosureLocatorVisitor;

/**
 * @covers SuperClosure\Analyzer\Visitor\ClosureLocatorVisitor
 */
class ClosureLocatorVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testClosureNodeIsDiscoveredByVisitor()
    {
        $closure = function () {}; $startLine = __LINE__;
        $reflectedClosure = new \ReflectionFunction($closure);
        $closureFinder = new ClosureLocatorVisitor($reflectedClosure);
        $closureNode = new \PhpParser\Node\Expr\Closure([], ['startLine' => $startLine]);
        $closureFinder->enterNode($closureNode);

        $this->assertSame($closureNode, $closureFinder->closureNode);
    }

    public function testClosureNodeIsAmbiguousIfMultipleClosuresOnLine()
    {
        $this->setExpectedException('RuntimeException');

        $closure = function () {}; function () {}; $startLine = __LINE__;
        $closureFinder = new ClosureLocatorVisitor(new \ReflectionFunction($closure));
        $closureFinder->enterNode(new \PhpParser\Node\Expr\Closure([], ['startLine' => $startLine]));
        $closureFinder->enterNode(new \PhpParser\Node\Expr\Closure([], ['startLine' => $startLine]));
    }

    public function testCalculatesClosureLocation()
    {
        $closure = function () {};
        $closureFinder = new ClosureLocatorVisitor(new \ReflectionFunction($closure));

        $closureFinder->beforeTraverse([]);

        $node = new \PhpParser\Node\Stmt\Namespace_(new \PhpParser\Node\Name(['Foo', 'Bar']));
        $closureFinder->enterNode($node);
        $closureFinder->leaveNode($node);

        $node = new \PhpParser\Node\Stmt\Trait_('Fizz');
        $closureFinder->enterNode($node);
        $closureFinder->leaveNode($node);

        $node = new \PhpParser\Node\Stmt\Class_('Buzz');
        $closureFinder->enterNode($node);
        $closureFinder->leaveNode($node);

        $closureFinder->afterTraverse([]);

        $actualLocationKeys = array_filter($closureFinder->location);
        $expectedLocationKeys = ['directory', 'file', 'function', 'line'];

        $this->assertEquals($expectedLocationKeys, array_keys($actualLocationKeys));
    }

    public function testCanDetermineClassOrTraitInfo()
    {
        $closure = function () {};
        $closureFinder = new ClosureLocatorVisitor(new \ReflectionFunction($closure));
        $closureFinder->location['namespace'] = __NAMESPACE__;

        $closureFinder->location['class'] = 'FooClass';
        $closureFinder->afterTraverse([]);
        $class = $closureFinder->location['class'];
        $this->assertEquals(__NAMESPACE__ . '\FooClass', $class);

        $closureFinder->location['class'] = null;
        $closureFinder->location['trait'] = 'FooTrait';
        $closureFinder->afterTraverse([]);
        $trait = $closureFinder->location['trait'];
        $this->assertEquals(__NAMESPACE__ . '\FooTrait', $trait);
    }
}
