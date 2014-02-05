<?php

namespace SuperClosure\ClosureParser;

use SuperClosure\SuperClosure;

interface ClosureParserInterface
{
    /**
     * @param \Closure|SuperClosure $closure
     *
     * @return ClosureContextInterface
     * @throws ClosureParsingException
     */
    public function parse($closure);
}
