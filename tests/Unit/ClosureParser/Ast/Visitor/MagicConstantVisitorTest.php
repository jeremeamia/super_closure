<?php namespace SuperClosure\Test\Unit\ClosureParser\Ast\Visitor;

use SuperClosure\ClosureParser\Ast\Visitor\MagicConstantVisitor;
use SuperClosure\ClosureParser\Ast\ClosureLocation;
use SuperClosure\Test\Unit\UnitTestBase;

/**
 * @covers SuperClosure\ClosureParser\Ast\Visitor\MagicConstantVisitor
 */
class MagicConstantVisitorTest extends UnitTestBase
{
    public function classNameProvider()
    {
        return array(
            array('PhpParser\Node\Scalar\MagicConst\Class_', 'PhpParser\Node\Scalar\String'),
            array('PhpParser\Node\Scalar\MagicConst\Dir', 'PhpParser\Node\Scalar\String'),
            array('PhpParser\Node\Scalar\MagicConst\File', 'PhpParser\Node\Scalar\String'),
            array('PhpParser\Node\Scalar\MagicConst\Function_', 'PhpParser\Node\Scalar\String'),
            array('PhpParser\Node\Scalar\MagicConst\Line', 'PhpParser\Node\Scalar\LNumber'),
            array('PhpParser\Node\Scalar\MagicConst\Method', 'PhpParser\Node\Scalar\String'),
            array('PhpParser\Node\Scalar\MagicConst\Namespace_', 'PhpParser\Node\Scalar\String'),
            array('PhpParser\Node\Scalar\MagicConst\Trait_', 'PhpParser\Node\Scalar\String'),
            array('PhpParser\Node\Scalar\String', 'PhpParser\Node\Scalar\String'),
        );
    }

    /**
     * @dataProvider classNameProvider
     */
    public function testDataFromClosureLocationGetsUsed($original, $result)
    {
        $location = new ClosureLocation();
        $visitor = new MagicConstantVisitor($location);

        $node = $this->getMockParserNode($original, strtr(substr(rtrim($original, '_'), 15), '\\', '_'));
        $resultNode = $visitor->leaveNode($node) ?: $node;

        $this->assertInstanceOf($result, $resultNode);
    }
}
