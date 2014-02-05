<?php

namespace SuperClosure\ClosureParser;

use SuperClosure\ClosureBinding;

interface ClosureContextInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return array
     */
    public function getVariables();

    /**
     * @return ClosureBinding
     */
    public function getBinding();
}
