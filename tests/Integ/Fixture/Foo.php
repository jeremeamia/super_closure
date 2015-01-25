<?php namespace SuperClosure\Test\Integ\Fixture;

class Foo
{
    private $bar;

    public function __construct($bar = null)
    {
        $this->bar = $bar;
    }

    public function getClosure()
    {
        return function () {
            return $this->bar;
        };
    }
}
