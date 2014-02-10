<?php

namespace SuperClosure\Test\Unit\ClosureParser;

use SuperClosure\ClosureParser\AbstractClosureParser;
use SuperClosure\ClosureParser\Options;

class ConcreteClosureParser extends AbstractClosureParser
{
    public function parse($closure)
    {
        return $this->prepareClosure($closure);
    }

    protected function getDefaultOptions()
    {
        return new Options;
    }
}
