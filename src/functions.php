<?php

use Jeremeamia\SuperClosure\SerializableClosure;
use Jeremeamia\SuperClosure\ClosureParser\ClosureParserFactory;
use Jeremeamia\SuperClosure\ClosureParser\ClosureParserInterface;

if (!defined('SC_TURBO_MODE')) {
    define('SC_HANDLE_CLASS_NAMES',      ClosureParserInterface::HANDLE_CLASS_NAMES);
    define('SC_HANDLE_CLOSURE_BINDINGS', ClosureParserInterface::HANDLE_CLOSURE_BINDINGS);
    define('SC_HANDLE_MAGIC_CONSTANTS',  ClosureParserInterface::HANDLE_MAGIC_CONSTANTS);
    define('SC_VALIDATE_TOKENS',         ClosureParserInterface::VALIDATE_TOKENS);
    define('SC_PARSER_AST',              ClosureParserFactory::AST_PARSER);
    define('SC_PARSER_CLASS',            ClosureParserFactory::PARSER_CLASS);
    define('SC_PARSER_TOKEN',            ClosureParserFactory::TOKEN_PARSER);
    define('SC_TURBO_MODE',              ClosureParserFactory::TURBO_MODE);
}

if (!function_exists('serialize_closure')) {
    /**
     * A convenient function that abstracts the use of the SerializableClosure object
     *
     * @param Closure                      $closure The closure to serialize
     * @param array|ClosureParserInterface $options An array of parsing options, or an instance of a parser
     *
     * @throws InvalidArgumentException if neither an array of options or and instance of a parser are provided
     * @return string The serialized closure
     */
    function serialize_closure(\Closure $closure, $options = array())
    {
        // The parser factory can be reused in subsequent calls
        static $parserFactory;

        // Get the parser
        if (is_array($options)) {
            // Instantiate a parser factory if needed
            if ($parserFactory === NULL) {
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
        $serializedClosure = serialize($serializableClosure);

        return $serializedClosure;
    }
}
