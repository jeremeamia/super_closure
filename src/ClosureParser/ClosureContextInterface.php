<?php

namespace Jeremeamia\SuperClosure\ClosureParser;

use Jeremeamia\SuperClosure\ClosureBinding;

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
