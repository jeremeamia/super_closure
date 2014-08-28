<?php

namespace Jeremeamia\SuperClosure\Test;

use Jeremeamia\SuperClosure\ClosureParser;

class ClosureParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::__construct
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getReflection
     */
    public function testCanGetReflectionBackFromParser()
    {
        $closure = function () {};
        $reflection = new \ReflectionFunction($closure);
        $parser = new ClosureParser($reflection);

        $this->assertSame($reflection, $parser->getReflection());
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::fromClosure
     */
    public function testCanUseFactoryMethodToCreateParser()
    {
        $parser = ClosureParser::fromClosure(function () {});

        $this->assertInstanceOf('Jeremeamia\SuperClosure\ClosureParser', $parser);
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::__construct
     */
    public function testRaisesErrorWhenNonClosureIsProvided()
    {
        $this->setExpectedException('InvalidArgumentException');

        $reflection = new \ReflectionFunction('strpos');
        $parser = new ClosureParser($reflection);
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getCode
     */
    public function testCanGetCodeFromParser()
    {
        $closure = function () {};
        $expectedCode = "function () {\n    \n};";
        $parser = new ClosureParser(new \ReflectionFunction($closure));
        $actualCode = $parser->getCode();

        $this->assertEquals($expectedCode, $actualCode);
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getContextFreeCode
     */
    public function testCanGetContextFreeCodeFromParser()
    {
        $context = 42;
        $closure = function () use ($context) {
            return $context * 2;
        };

        $expectedCode = "function () {\n    \$context = 42;\n    return \$context * 2;\n};";
        $parser = new ClosureParser(new \ReflectionFunction($closure));
        $actualCode = $parser->getContextFreeCode();

        $this->assertEquals($expectedCode, $actualCode);
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getContextFreeCode
     */
    public function testRaisesErrorWhenClosureHasThis()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Closure has $this variable and cannot be context free'
        );

        $parser = new ClosureParser(new \ReflectionFunction(function () {
            return $this->getCount();
        }));
        $parser->getContextFreeCode();
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getContextFreeCode
     */
    public function testRaisesErrorWhenClosureHasPassedByRefVars()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Variable "context" is passed by ref'
        );

        $context = 42;
        $parser = new ClosureParser(new \ReflectionFunction(function () use (&$context) {
            $context++;
        }));
        $parser->getContextFreeCode();
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getContextFreeCode
     */
    public function testRaisesErrorWhenClosureHasNonScalarValuesPassedThroughUse()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Only scalar values and arrays are allowed'
        );

        $context = new \stdClass();
        $parser = new ClosureParser(new \ReflectionFunction(function () use ($context) {
            return $context;
        }));
        $parser->getContextFreeCode();
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getUsedVariables
     */
    public function testCanGetUsedVariablesFromParser()
    {
        $foo = 1;
        $bar = 2;
        $closure = function () use ($foo, $bar) {};
        $expectedVars = array('foo' => 1, 'bar' => 2);
        $parser = new ClosureParser(new \ReflectionFunction($closure));
        $actualVars = $parser->getUsedVariables();

        $this->assertEquals($expectedVars, $actualVars);
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::clearCache
     */
    public function testCanClearCache()
    {
        $parserClass = 'Jeremeamia\SuperClosure\ClosureParser';

        $p = new \ReflectionProperty($parserClass, 'cache');
        $p->setAccessible(true);
        $p->setValue(null, array('foo' => 'bar'));

        $this->assertEquals(array('foo' => 'bar'), $this->readAttribute($parserClass, 'cache'));

        ClosureParser::clearCache();

        $this->assertEquals(array(), $this->readAttribute($parserClass, 'cache'));
    }

    /**
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getClosureAbstractSyntaxTree
     * @covers \Jeremeamia\SuperClosure\ClosureParser::getFileAbstractSyntaxTree
     */
    public function testCanGetClosureAst()
    {
        $closure = function () {};
        $parser = new ClosureParser(new \ReflectionFunction($closure));
        $ast = $parser->getClosureAbstractSyntaxTree();
        $this->assertInstanceOf('PHPParser_Node_Expr_Closure', $ast);
    }
}
