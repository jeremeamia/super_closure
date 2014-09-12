<?php

namespace Jeremeamia\SuperClosure\Test\Visitor;

use Jeremeamia\SuperClosure\Visitor\MagicConstantVisitor;
use Jeremeamia\SuperClosure\ClosureLocation;

/**
 * @covers Jeremeamia\SuperClosure\Visitor\MagicConstantVisitor
 */
class MagicConstantVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function testDataFromClosureLocationGetsUsed()
    {
        $location = new ClosureLocation();
        $location->class = '[class]';
        $location->directory = '[directory]';
        $location->file = '[file]';
        $location->function = '[function]';
        $location->line = '[line]';
        $location->method = '[method]';
        $location->namespace = '[namespace]';
        $location->trait = '[trait]';

        $nodes = array(
            'PhpParser\Node\Scalar\MagicConst\Line'       => 'PhpParser\Node\Scalar\LNumber',
            'PhpParser\Node\Scalar\MagicConst\File'       => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Dir'        => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Function_'  => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Namespace_' => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Class_'     => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Method'     => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\MagicConst\Trait_'     => 'PhpParser\Node\Scalar\String',
            'PhpParser\Node\Scalar\String'                => 'PhpParser\Node\Scalar\String',

        );

        $visitor = new MagicConstantVisitor($location);
        foreach ($nodes as $originalNodeName => $resultNodeName) {
            $mockNode = $this->getMockBuilder($originalNodeName)
                ->disableOriginalConstructor()
                ->setMethods(array('getType', 'getAttribute'))
                ->getMock();
            $mockNode->expects($this->any())
                ->method('getAttribute')
                ->will($this->returnValue(1));

            $mockNode->expects($this->any())
                ->method('getType')
                ->will($this->returnValue($this->constFromOriginalNodeName($originalNodeName)));
            $resultNode = $visitor->leaveNode($mockNode) ?: $mockNode;

            $this->assertInstanceOf($resultNodeName, $resultNode);
        }
    }

    /**
     * Takes a fully namespaced name and generates the PHP-Parser Scalar_*
     * const string
     *
     * @param string $originalNodeNmae
     *
     * @return string
     */
    private function constFromOriginalNodeName($originalNodeName)
    {
        return rtrim(str_replace("\\", "_", substr($originalNodeName, 15)), "_");
    }
}
