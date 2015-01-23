<?php namespace SuperClosure;

use SuperClosure\Exception\ClosureUnserializationException;

/**
 * This class acts as a wrapper for a closure, and allows it to be serialized.
 *
 * With the combined power of the Reflection API, code parsing, and the infamous
 * `eval()` function, you can serialize a closure, unserialize it somewhere
 * else (even a different PHP process), and execute it.
 */
class SerializableClosure implements \Serializable
{
    /** @var \Closure Closure being wrapped for serialization. */
    private $closure;

    /** @var SerializerInterface Serializer doing the serialization work. */
    private $serializer;

    /** @var array Temporary data container used during unserialization. */
    private $temp;

    /**
     * @param \Closure            $closure
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Closure $closure,
        SerializerInterface $serializer = null
    ) {
        $this->closure = $closure;
        $this->serializer = $serializer ?: new Serializer;
    }

    /**
     * Return the original closure object.
     *
     * @return \Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * Delegates the closure invocation to the actual closure object.
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
     * Serializes the code, context, and binding of the closure.
     *
     * @return string
     * @see http://php.net/manual/en/serializable.serialize.php
     */
    public function serialize()
    {
        try {
            return serialize($this->serializer->getData($this->closure, true));
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
     * Unserializes the closure.
     *
     * Unserializes the closure's data and recreates the closure using a
     * simulation of its original context. The used variables (context) are
     * extracted into a fresh scope prior to redefining the closure. The
     * closure is also rebound to its former object and scope.
     *
     * @param string $serialized
     *
     * @throws ClosureUnserializationException
     * @see http://php.net/manual/en/serializable.unserialize.php
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

    /**
     * Returns closure data for `var_dump()`.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->serializer->getData($this->closure);
    }
}
