<?php

namespace SuperClosure\Test\Unit;

use SuperClosure;
use SuperClosure\ClosureParser\Options;

/**
 * @coversNothing
 */
class FunctionsTest extends UnitTestBase
{
    public function testSimpleSerializationWithTurboMode()
    {
        $closureBefore = function() {return 'foo';};
        $serialization = SuperClosure\serialize($closureBefore, SuperClosure\TURBO_MODE);
        /** @var \SuperClosure\SerializableClosure $unserialization */
        $unserialization = unserialize($serialization);
        $closureAfter = $unserialization->getClosure();

        $this->assertInstanceOf('Closure', $closureAfter);
        $this->assertEquals('foo', $closureAfter());
    }

    public function testSimpleSerializationWithTraversableOptions()
    {
        $closureBefore = function() {return 'foo';};
        $options = new Options(array(Options::HANDLE_CLOSURE_BINDINGS => false));
        $serialization = SuperClosure\serialize($closureBefore, $options);
        /** @var \SuperClosure\SerializableClosure $unserialization */
        $unserialization = unserialize($serialization);
        $closureAfter = $unserialization->getClosure();

        $this->assertInstanceOf('Closure', $closureAfter);
        $this->assertEquals('foo', $closureAfter());
    }

    public function testSerializationWhenProvidingParser()
    {
        $parser = $this->getMockClosureParser();
        $closure = function() {};
        $expectedSerialization = self::DUMMY_SERIALIZED_CLOSURE;
        $actualSerialization = SuperClosure\serialize($closure, $parser);

        $this->assertEquals($expectedSerialization, $actualSerialization);
    }

    public function testSerializationFailsOnBadOptionsInput()
    {
        $this->setExpectedException('InvalidArgumentException');

        $closure = function() {};
        SuperClosure\serialize($closure, 'foo');
    }

    public function testClassAliasWorks()
    {
        $this->assertTrue(class_exists('SuperClosure\SerializableClosure'));
        $this->assertTrue(class_exists('Jeremeamia\SuperClosure\SerializableClosure'));
    }
}
