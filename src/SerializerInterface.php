<?php namespace SuperClosure;

/**
 * Serializer is used to serialize Closure objects, abstracting away all the
 * details, impossibilities, and scary things that happen within.
 */
interface SerializerInterface
{
    /**
     * Takes a Closure object, decorates it with a SerializableClosure object,
     * then performs the serialization.
     *
     * @param \Closure $closure Closure to serialize.
     *
     * @return string Serialized closure.
     */
    public function serialize(\Closure $closure);

    /**
     * Takes a serialized closure, performs the unserialization, and then
     * extracts and returns a the Closure object.
     *
     * @param string $serialized Serialized closure.
     *
     * @return \Closure Unserialized closure.
     */
    public function unserialize($serialized);

    /**
     * Retrieves data about a closure including its code, context, and binding.
     *
     * The data returned is dependant on the `ClosureAnalyzer` implementation
     * used and whether the `$forSerialization` parameter is set to true. If
     * `$forSerialization` is true, then only data relevant to serializing the
     * closure is returned.
     *
     * @param \Closure $closure          Closure to analyze.
     * @param bool     $forSerialization Include only serialization data.
     *
     * @return \Closure
     */
    public function getData(\Closure $closure, $forSerialization = false);
}
