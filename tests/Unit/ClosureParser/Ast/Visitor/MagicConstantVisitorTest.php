<?php namespace SuperClosure\Test\Unit\ClosureParser\Ast\Visitor;

use SuperClosure\ClosureParser\Ast\Visitor\MagicConstantVisitor;
use SuperClosure\ClosureParser\Ast\ClosureLocation;
use SuperClosure\Test\Unit\UnitTestBase;

/**
 * @covers SuperClosure\ClosureParser\Ast\Visitor\MagicConstantVisitor
 */
class MagicConstantVisitorTest extends UnitTestBase
{
    public function testDataFromClosureLocationGetsUsed()
    {
        $location = new ClosureLocation();

        $nodes = array(
            'PhpParser\Node\Scalar\MagicConst\Class_'     => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Dir'        => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\File'       => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Function_'  => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Line'       => 'PhpParser\Node\Scalar\LNumber',
            'PhpParser\Node\Scalar\MagicConst\Method'     => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Namespace_' => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Trait_'     => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\String'                => 'PhpParser\Node\Scalar\String',
        );

        $visitor = new MagicConstantVisitor($location);
        foreach ($nodes as $originalNodeName => $resultNodeName) {
            $mockNode = $this->getMockParserNode($originalNodeName, substr($originalNodeName, 15), 1);
            $resultNode = $visitor->leaveNode($mockNode) ?: $mockNode;
            $this->assertInstanceOf($resultNodeName, $resultNode);
        }
    }
}
