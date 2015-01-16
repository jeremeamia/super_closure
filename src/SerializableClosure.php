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

    /** @var array */
    private $temp;

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
     * extracted into a fresh scope prior to redefining the closure. The
     * closure is also rebound to its former object and scope.
     *
     * @param string $serialized
     *
     * @throws ClosureUnserializationException
     */
    public function unserialize($serialized)
    {
        // Unserialize the data and reconstruct the SuperClosure.
        $this->temp = unserialize($serialized);
        $this->serializer = $this->temp['serializer'];
        $this->reconstructClosure();
        if (!$this->closure instanceof \Closure) {
            throw new ClosureUnserializationException(
                'The closure is corrupted and cannot be unserialized.'
            );
        }

        // Rebind the closure to its former $this object and scope, if defined,
        // otherwise, bind to null so it's not bound to SerializableClosure.
        $this->closure = $this->closure->bindTo(
            $this->temp['binding'],
            $this->temp['scope']
        );

        // Clear temp data used during unserialization.
        unset($this->temp);
    }

    /**
     * HERE BE DRAGONS!
     *
     * The infamous `eval()` is used in this method, along with `extract()`,
     * the error suppression operator, and variable variables (i.e., double
     * dollar signs) to perform the unserialization work. I'm sorry, world!
     */
    private function reconstructClosure()
    {
        // Simulate the original context the closure was created in.
        extract($this->temp['context'], EXTR_OVERWRITE);

        // Evaluate the code to recreate the closure.
        if ($_fn = array_search(Serializer::RECURSION, $this->temp['context'], true)) {
            @eval("\${$_fn} = {$this->temp['code']};");
            $this->closure = $$_fn;
        } else {
            @eval("\$this->closure = {$this->temp['code']};");
        }
    }
}
