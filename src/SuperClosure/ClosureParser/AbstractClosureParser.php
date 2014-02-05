<?php

namespace SuperClosure\ClosureParser;

use SuperClosure\SuperClosure;

abstract class AbstractClosureParser implements ClosureParserInterface
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * @param Options $options
     */
    public function __construct(Options $options = null)
    {
        $this->options = $this->getDefaultOptions();
        if ($options) {
            $this->options->merge($options);
        }
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
            return new SuperClosure($closure);
        }

        if ($closure instanceof SuperClosure) {
            return $closure;
        }

        throw new \InvalidArgumentException('You must provide a PHP Closure or SuperClosure object to be parsed.');
    }

    /**
     * @return Options
     */
    abstract protected function getDefaultOptions();
}
