<?php

namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\ClosureLocation;
use SuperClosure\Test\Unit\UnitTestBase;

/**
 * @covers \SuperClosure\ClosureParser\ClosureLocation
 */
class ClosureLocationTest extends UnitTestBase
{
    public function testCanCreateClosureLocation()
    {
        $keys = array('class', 'directory', 'file', 'function', 'line', 'method', 'namespace', 'trait');
        $data = array_combine($keys, array_map(function ($value) {return "[{$value}]";}, $keys));
        $location = new ClosureLocation($data);
        foreach ($keys as $key) {
            $this->assertEquals("[{$key}]", $location->{$key});
        }
    }
}
