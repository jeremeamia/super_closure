<?php

namespace
{
    // This alias exists to provide backwards compatibility with version 1.0 of SuperClosure.
    class_alias('SuperClosure\SerializableClosure', 'Jeremeamia\SuperClosure\SerializableClosure');
}

namespace SuperClosure
{
    use SuperClosure\ClosureParser\ClosureParserFactory;
    use SuperClosure\ClosureParser\ClosureParserInterface;
    use SuperClosure\ClosureParser\Options;

    const TURBO_MODE = 'turbo_mode';

    /**
     * A convenient function that abstracts the use of the SerializableClosure object
     *
     * @param \Closure                            $closure The closure to serialize
     * @param array|ClosureParserInterface|string $options An array of parsing options, or an instance of a parser
     *
     * @throws \InvalidArgumentException if neither an array of options or and instance of a parser are provided
     * @return string The serialized closure
     */
    function serialize(\Closure $closure, $options = array())
    {
        // The parser factory can be reused in subsequent calls
        static $parserFactory;

        // Do some special handling for turbo mode and traversable objects
        if ($options === TURBO_MODE) {
            $options = array(
                Options::HANDLE_CLOSURE_BINDINGS => false,
                Options::HANDLE_MAGIC_CONSTANTS  => false,
                Options::HANDLE_CLASS_NAMES      => false,
                Options::VALIDATE_TOKENS         => false,
            );
        } elseif ($options instanceof \Traversable) {
            $options = iterator_to_array($options);
        }

        // Get the parser
        if (is_array($options)) {
            // Instantiate a parser factory if needed
            if ($parserFactory === null) {
                $parserFactory = new ClosureParserFactory;
            }

            // Create the parser with the provided options
            $parser = $parserFactory->create($options);
        } elseif ($options instanceof ClosureParserInterface) {
            // Use the injected parser
            $parser = $options;
        } else {
            throw new \InvalidArgumentException('You must provide an array of options or an instance of a parser.');
        }

        // Serialize the closure
        $serializableClosure = new SerializableClosure($closure, $parser);
        $serializedClosure = \serialize($serializableClosure);

        return $serializedClosure;
    }
}
