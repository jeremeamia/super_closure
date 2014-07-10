<?php namespace SuperClosure;

class SuperClosure
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var \ReflectionFunction
     */
    protected $reflection;

    /**
     * @var ClosureBinding
     */
    protected $binding;

    /**
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @return \Closure
     */
    public function getClosure()
    {
        return $this->closure;
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
     * @return ClosureBinding
     */
    public function getBinding()
    {
        if (!$this->binding) {
            $this->binding = ClosureBinding::fromReflection($this->getReflection());
        }

        return $this->binding;
    }

    /**
     * @param object        $object Object to which the closure should be bound,
     *                              or `NULL` for the closure to be unbound. If
     *                              a `ClosureBinding` object is provided, the
     *                              information from the `ClosureBinding` will
     *                              be used to do the internal `bindTo()`
     * @param string|object $scope  The class scope to which associate the
     *                              closure is to be associated, or "static" to
     *                              keep the current one. If an object is given,
     *                              the type of the object will be used instead.
     *                              This determines the visibility of protected
     *                              and private methods of the bound object.
     *
     * @return $this
     * @throws \RuntimeException
     */
    public function bindTo($object, $scope = 'static')
    {
        if (!Env::supportsBindings()) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('Closure binding is a feature of PHP 5.4+.');
            // @codeCoverageIgnoreEnd
        }

        // If a ClosureBinding object is provided, pull out the binding information to use
        if ($object instanceof ClosureBinding) {
            $scope = $object->getScope();
            $object = $object->getObject();
        }

        // Bind the closure to its new object and scope and remove the cached reflection and binding objects
        $this->closure = $this->closure->bindTo($object, $scope);
        $this->reflection = null;
        $this->binding = null;

        return $this;
    }

    /**
     * Delegate the Closure invocation to the actual closure object using PHP's pre-5.6 pseudo-variadics.
     *
     * Important Notes:
     *
     * - `ReflectionFunction::invokeArgs()` should not be used here, because it does not work with closure bindings.
     * - Args passed-by-reference lose their references when proxied through `__invoke()`. This is is an unfortunate,
     *   but understandable, limitation of PHP that will probably never change.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->closure, func_get_args());
    }
}
