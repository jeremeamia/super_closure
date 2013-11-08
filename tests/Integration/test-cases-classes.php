<?php

namespace
{
    class Foo
    {
        protected $bar;

        public function __construct($bar = null)
        {
            $this->bar = $bar;
        }

        public function getClosure()
        {
            return function() {
                return $this->bar;
            };
        }
    }
}

namespace Things
{
    class Queue extends \SplQueue {}

    class Test
    {
        public function getMagicClosure()
        {
            return function() {
                return '[' . __NAMESPACE__ . '|' . __CLASS__ . ']';
            };
        }
    }
}

namespace Distraction
{
    class AreYouDistracted {}
}
