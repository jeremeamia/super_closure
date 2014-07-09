<?php

namespace
{
    // This alias exists to provide backwards compatibility with version 1.0 of SuperClosure.
    class_alias('SuperClosure\SerializableClosure', 'Jeremeamia\SuperClosure\SerializableClosure');
}

namespace SuperClosure
{
    use SuperClosure\ClosureParser\ClosureParser;

    const TURBO_MODE = 'turbo_mode';

    /**
     * A convenient function that abstracts the use of the SerializableClosure object
     *
     * @param \Closure                            $closure The closure to serialize
     * @param array|ClosureParser|string $options An array of parsing options, or an instance of a parser
     *
     * @throws \InvalidArgumentException if neither an array of options or and instance of a parser are provided
     * @return string The serialized closure
     */
    function serialize(\Closure $closure, $options = array())
    {
        // Do some special handling for turbo mode and traversable objects
        if ($options === TURBO_MODE) {
            $options = array(
                ClosureParser::HANDLE_CLOSURE_BINDINGS => false,
                ClosureParser::HANDLE_MAGIC_CONSTANTS  => false,
                ClosureParser::HANDLE_CLASS_NAMES      => false,
                ClosureParser::VALIDATE_TOKENS         => false,
            );
        }

        // Get the parser
        if (is_array($options)) {
            // Create the parser with the provided options
            $parser = ClosureParser::create($options);
        } elseif ($options instanceof ClosureParser) {
            // Use the injected parser
            $parser = $options;
        } else {
            throw new \InvalidArgumentException(
                'Please provide an array of options or an instance of a parser.'
            );
        }

        // Serialize the closure
        $serializableClosure = new SerializableClosure($closure, $parser);
        $serializedClosure = \serialize($serializableClosure);

        return $serializedClosure;
    }
}
