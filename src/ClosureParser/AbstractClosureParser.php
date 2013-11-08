<?php

namespace Jeremeamia\SuperClosure\ClosureParser;

use Jeremeamia\SuperClosure\SuperClosure;

abstract class AbstractClosureParser implements ClosureParserInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + $this->getDefaultOptions();
    }

    /**
     * @param \Closure|SuperClosure $closure
     *
     * @return SuperClosure
     * @throws \InvalidArgumentException
     */
    protected function prepareClosure($closure)
    {
        if ($closure instanceof \Closure) {
            $closure = new SuperClosure($closure);
        }

        if ($closure instanceof SuperClosure) {
            return $closure;
        }

        throw new \InvalidArgumentException('You must provide a PHP Closure or SuperClosure object to be parsed.');
    }

    /**
     * @return array
     */
    abstract protected function getDefaultOptions();
}
