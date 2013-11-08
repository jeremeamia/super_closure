<?php

namespace Jeremeamia\SuperClosure\ClosureParser;

use Jeremeamia\SuperClosure\ClosureBinding;

abstract class AbstractClosureContext implements ClosureContextInterface
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var ClosureBinding
     */
    protected $binding;

    /**
     * @param string         $code
     * @param array          $variables
     * @param ClosureBinding $binding
     */
    public function __construct($code, array $variables, ClosureBinding $binding = null)
    {
        $this->code = (string) $code;
        $this->variables = $variables;
        $this->binding = $binding;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @return ClosureBinding
     */
    public function getBinding()
    {
        return $this->binding;
    }
}
