<?php namespace SuperClosure\Test\Unit\Analyzer\Visitor;

use SuperClosure\Analyzer\Visitor\MagicConstantVisitor;

/**
 * @covers SuperClosure\Analyzer\Visitor\MagicConstantVisitor
 */
class MagicConstantVisitorTest extends \PHPUnit_Framework_TestCase
{
    public function classNameProvider()
    {
        return [
            ['PhpParser\Node\Scalar\MagicConst\Class_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Dir', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\File', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Function_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Line', 'PhpParser\Node\Scalar\LNumber'],
            ['PhpParser\Node\Scalar\MagicConst\Method', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Namespace_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Trait_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\String_', 'PhpParser\Node\Scalar\String_'],
        ];
    }

    /**
     * @dataProvider classNameProvider
     */
    public function testDataFromClosureLocationGetsUsed($original, $result)
    {
        $location = [
            'class'     => null,
            'directory' => null,
            'file'      => null,
            'function'  => null,
            'line'      => null,
            'method'    => null,
            'namespace' => null,
            'trait'     => null,
        ];

        $visitor = new MagicConstantVisitor($location);

        $node = $this->getMockParserNode($original, strtr(substr(rtrim($original, '_'), 15), '\\', '_'));
        $resultNode = $visitor->leaveNode($node) ?: $node;

        $this->assertInstanceOf($result, $resultNode);
    }

    /**
     * @param string      $class
     * @param string|null $type
     * @param string|null $attribute
     *
     * @return \PhpParser\NodeAbstract
     */
    public function getMockParserNode($class, $type = null, $attribute = null)
    {
        $node = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods(['getType', 'getAttribute'])
            ->getMock();
        $node->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));
        $node->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));
        return $node;
    }
}
