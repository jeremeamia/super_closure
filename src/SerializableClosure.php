<?php namespace SuperClosure;

use SuperClosure\Exception\ClosureUnserializationException;

/**
 * This class allows you to do the impossible: serialize closures! With the
 * combined power of lexical parsing, the Reflection API, and the infamous
 * `eval()` function, you can serialize a closure, unserialize it in a different
 * PHP process, and execute it. It's like function teleportation!
 */
class SerializableClosure implements \Serializable
{
    /** @var \Closure */
    private $closure;

    /** @var Serializer */
    private $serializer;

    /**
     * @param \Closure   $closure
     * @param Serializer $serializer
     */
    public function __construct(\Closure $closure, Serializer $serializer = null)
    {
        $this->closure = $closure;
        $this->serializer = $serializer ?: new Serializer;
    }

    /**
     * @return \Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Delegate the Closure invocation to the actual closure object.
     *
     * Important Notes:
     *
     * - `ReflectionFunction::invokeArgs()` should not be used here, because it
     *   does not work with closure bindings.
     * - Args passed-by-reference lose their references when proxied through
     *   `__invoke()`. This is is an unfortunate, but understandable, limitation
     *   of PHP that will probably never change.
     *
     * @return mixed
     */
    public function __invoke()
    {
        return call_user_func_array($this->closure, func_get_args());
    }

    /**
     * Serialize the code and context of the closure.
     *
     * @return string
     */
    public function serialize()
    {
        try {
            return serialize($this->serializer->getClosureData($this->closure, true));
        } catch (\Exception $e) {
            trigger_error(
                'Serialization of closure failed: ' . $e->getMessage(),
                E_USER_NOTICE
            );
            // Note: The serialize() method of Serializable must return a string
            // or null and cannot throw exceptions.
            return null;
        }
    }

    /**
     * Unserializes the Closure.
     *
     * Unserializes the closure's data and recreates the closure using a
     * simulation of its original context. The used variables (context) are
     * extracted into the scope prior to redefining the closure. If the
     * closure's binding was serialized (PHP 5.4+), then the closure will also
     * be rebound to its former object and scope.
     *
     * NOTE: HERE BE DRAGONS! The infamous `eval()` is used in this method to
     * perform the unserialization/hydration work. Sorry, it is the only way.
     *
     * @param string $serialized
     *
     * @throws ClosureUnserializationException
     */
    public function unserialize($serialized)
    {
        // Unserialize the data we need to reconstruct the SuperClosure.
        $_data = \unserialize($serialized);
        $this->serializer = $_data['serializer'];

        // Simulate the original context the closure was created in.
        extract($_data['context'], EXTR_OVERWRITE);

        // Evaluate the code to recreate the closure.
        if ($_fn = array_search(Serializer::RECURSION, $_data['context'], true)) {
            @eval("\${$_fn} = {$_data['code']};");
            $this->closure = $$_fn;
        } else {
            @eval("\$this->closure = {$_data['code']};");
        }
        if (!$this->closure instanceof \Closure) {
            throw new ClosureUnserializationException(
                'The closure was corrupted and cannot be unserialized.'
            );
        }

        // Rebind the closure to its former $this object and scope, if defined,
        // otherwise, bind to null so it's not bound to SerializableClosure.
        $this->closure = $this->closure->bindTo($_data['binding'], $_data['scope']);
    }
}
