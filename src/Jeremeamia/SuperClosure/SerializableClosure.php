<?php

namespace Jeremeamia\SuperClosure;

/**
 * This class allows you to do the impossible – serializing closures! With the combined power of the nikic/php-parser
 * library, the Reflection API, and eval, you can serialize a closure.
 */
class SerializableClosure implements \Serializable
{
    /**
     * @var \Closure The closure being made serializable
     */
    protected $closure;

    /**
     * @var \ReflectionFunction The reflected closure
     */
    protected $reflection;

    /**
     * @var array Serialized state
     */
    private $state;

    /**
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @return \ReflectionFunction
     */
    public function getReflection()
    {
        if (!$this->reflection) {
            $this->reflection = new \ReflectionFunction($this->closure);
        }

        return $this->reflection;
    }

    /**
     * @return \Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Invokes the original closure
     *
     * @return mixed
     */
    public function __invoke()
    {
        return $this->getReflection()->invokeArgs(func_get_args());
    }

    /**
     * Uses the closure parser to fetch the closure's code. The code and the closure's context are serialized
     *
     * @return string
     */
    public function serialize()
    {
        if (!$this->state) {
            $parser = new ClosureParser($this->getReflection());
            $this->state = array(
                $parser->getCode(),
                array_map(function ($var) {
                    return ($var instanceof \Closure) ? new self($var) : $var;
                }, $parser->getUsedVariables())
            );
        }

        return serialize($this->state);
    }

    /**
     * Unserializes the closure data and recreates the closure. Attempts to recreate the closure's context as well by
     * extracting the used variables into the scope. Variables names in this method are surrounded with underlines in
     * order to prevent collisions with the variables in the context. NOTE: There be dragons here! Both `eval` and
     * `extract` are used in this method
     *
     * @param string $__serialized__
     */
    public function unserialize($__serialized__)
    {
        // Unserialize the data we need to reconstruct the SuperClosure
        $this->state = unserialize($__serialized__);
        list($__code__, $__context__) = $this->state;

        // Simulate the original context the Closure was created in
        extract($__context__);

        // Evaluate the code to recreate the Closure
        eval("\$this->closure = {$__code__};");
    }
}
