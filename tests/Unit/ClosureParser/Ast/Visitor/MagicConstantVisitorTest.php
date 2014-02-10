<?php

namespace SuperClosure\Test\Unit\ClosureParser\Ast\Visitor;

use SuperClosure\ClosureParser\Ast\Visitor\MagicConstantVisitor;
use SuperClosure\ClosureParser\ClosureLocation;
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
            'PHPParser_Node_Scalar_LineConst'   => 'PHPParser_Node_Scalar_LNumber',
            'PHPParser_Node_Scalar_FileConst'   => 'PHPParser_Node_Scalar_String',
            'PHPParser_Node_Scalar_DirConst'    => 'PHPParser_Node_Scalar_String',
            'PHPParser_Node_Scalar_FuncConst'   => 'PHPParser_Node_Scalar_String',
            'PHPParser_Node_Scalar_NSConst'     => 'PHPParser_Node_Scalar_String',
            'PHPParser_Node_Scalar_ClassConst'  => 'PHPParser_Node_Scalar_String',
            'PHPParser_Node_Scalar_MethodConst' => 'PHPParser_Node_Scalar_String',
            'PHPParser_Node_Scalar_TraitConst'  => 'PHPParser_Node_Scalar_String',
            'PHPParser_Node_Scalar_String'      => 'PHPParser_Node_Scalar_String',
        );

        $visitor = new MagicConstantVisitor($location);
        foreach ($nodes as $originalNodeName => $resultNodeName) {
            $mockNode = $this->getMockParserNode($originalNodeName, substr($originalNodeName, 15), 1);
            $resultNode = $visitor->leaveNode($mockNode) ?: $mockNode;
            $this->assertInstanceOf($resultNodeName, $resultNode);
        }
    }
}
