<?php

namespace SuperClosure;

use FunctionParser\FunctionParser,
    FunctionParser\Tokenizer,
    Closure,
    ReflectionFunction,
    Serializable;

/**
 * SuperClosure
 *
 * The SuperClosure class encapsulates a PHP Closure and adds new capabilities like serialization and code retrieval.
 * It uses the FunctionParser library to acquire information about the Closure to aid in serialization. Because the
 * class works with Closures, it requires PHP version 5.3+. DISCLAIMERS: This class is not designed to perform well due
 * to the nature of the techniques it uses. Also, you should note that it uses the `extract()` and `eval()` functions to
 * make serialization/unserialization possible.
 *
 * @package SuperClosure
 * @author  Jeremy Lindblom
 * @license MIT
 */
class SuperClosure extends FunctionParser implements Serializable
{
    /**
     * @var \Closure The closure being made super
     */
    protected $closure;

    /**
     * @var \ReflectionFunction The reflected closure.
     * @override
     */
    protected $reflection;

    /**
     * ??
     *
     * @param Closure $closure ??
     */
    public function __construct(Closure $closure)
    {
        $this->closure = $closure;

        parent::__construct(new ReflectionFunction($closure));
    }

    /**
     * ??
     *
     * @return Closure ??
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Delegate to the closure when this class is invoked as a method.
     *
     * @return mixed The return value of the closure.
     */
    public function __invoke()
    {
        return $this->reflection->invokeArgs(func_get_args());
    }

    /**
     * ??
     *
     * Closures (and reflected closures) cannot be serialized. The code and context will be serialized so that the
     * closure can be reconstructed.
     *
     * @return string ??
     */
    public function serialize()
    {
        return serialize(array($this->getCode(), $this->getContext()));
    }

    /**
     * ??
     *
     * @param $serialized ??
     */
    public function unserialize($serialized)
    {
        // Unserialize the data we need to reconstruct the SuperClosure
        list($code, $context) = unserialize($serialized);

        // Setup a safe scope to create the closure
        $buildClosure = function($code, $context)
        {
            // Simulate the original context the Closure was created in
            extract($context);

            // Evaluate the code to recreate the Closure
            eval("\$_closure = $code;");

            /** @var $_closure Closure */
            return $_closure;
        };

        // Rebuild the SuperClosure
        $this->closure    = $buildClosure($code, $context);
        $this->reflection = new ReflectionFunction($this->closure);
        $this->tokenizer  = new Tokenizer($code);
        $this->parameters = $this->fetchParameters();
        $this->code       = $code;
        $this->body       = $this->parseBody();
        $this->context    = $context;
    }
}
