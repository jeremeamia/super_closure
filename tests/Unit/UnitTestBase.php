<?php

namespace SuperClosure\Test\Unit;

use SuperClosure\ClosureBinding;
use SuperClosure\ClosureParser\ClosureContext;
use SuperClosure\ClosureParser\ClosureParser;

abstract class UnitTestBase extends \PHPUnit_Framework_TestCase
{
    const DUMMY_SERIALIZED_CLOSURE = 'C:32:"SuperClosure\SerializableClosure":33:{a:3:{i:0;s:0:"";i:1;a:0:{}i:2;N;}}';
    const SIMPLE_SERIALIZED_CLOSURE = 'C:32:"SuperClosure\SerializableClosure":46:{a:3:{i:0;s:12:"function(){}";i:1;a:0:{}i:2;N;}}';

    /**
     * @param string $code
     * @param array  $variables
     * @param null   $binding
     *
     * @return ClosureContext
     */
    public function getMockClosureContext($code = '', $variables = array(), $binding = null)
    {
        $context = $this->getMockBuilder('SuperClosure\\ClosureParser\\ClosureContext')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())->method('getCode')->will($this->returnValue($code));
        $context->expects($this->any())->method('getVariables')->will($this->returnValue($variables));
        $context->expects($this->any())->method('getBinding')->will($this->returnValue($binding));

        return $context;
    }

    /**
     * @param ClosureContext $context
     *
     * @return ClosureParser
     */
    public function getMockClosureParser(ClosureContext $context = null)
    {
        if (!$context) {
            $context = $this->getMockClosureContext();
        }

        $parser = $this->getMock('SuperClosure\\ClosureParser\\ClosureParser');
        $parser->expects($this->any())->method('parse')->will($this->returnValue($context));

        return $parser;
    }

    /**
     * @return ClosureBinding
     */
    public function getMockClosureBinding()
    {
        $binding = $this->getMockBuilder('SuperClosure\\ClosureBinding')->disableOriginalConstructor()->getMock();
        $binding->expects($this->any())->method('getObject')->will($this->returnValue(null));
        $binding->expects($this->any())->method('getScope')->will($this->returnValue(null));

        return $binding;
    }

    /**
     * @param string      $class
     * @param string|null $type
     * @param string|null $attribute
     *
     * @return \PHPParser_NodeAbstract
     */
    public function getMockParserNode($class, $type = null, $attribute = null)
    {
        $node = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->setMethods(array('getType', 'getAttribute'))
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
