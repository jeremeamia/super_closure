<?php

namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\Options;
use SuperClosure\Test\Unit\UnitTestBase;

/**
 * @covers \SuperClosure\ClosureParser\Options
 */
class OptionsTest extends UnitTestBase
{
    public function testMergingBehaviorsWorkCorrectly()
    {
        $options = Options::fromDefaults(array(Options::HANDLE_MAGIC_CONSTANTS => false));

        $expectedOptionsArrayAfterInstantiation = array(
            Options::HANDLE_CLOSURE_BINDINGS => true,
            Options::HANDLE_MAGIC_CONSTANTS  => false,
            Options::HANDLE_CLASS_NAMES      => true,
            Options::VALIDATE_TOKENS         => true,
        );

        $this->assertEquals($expectedOptionsArrayAfterInstantiation, $options->getArrayCopy());
    }
}
