<?php

namespace SuperClosure\ClosureParser;

use SuperClosure\ClosureParser\Ast\AstParser;
use SuperClosure\ClosureParser\Token\TokenParser;

class ClosureParserFactory
{
    /**
     * @var array
     */
    protected $defaultOptions = array(
        Options::HANDLE_CLOSURE_BINDINGS => true,
        Options::HANDLE_MAGIC_CONSTANTS  => true,
        Options::HANDLE_CLASS_NAMES      => true,
        Options::VALIDATE_TOKENS         => true,
    );

    /**
     * @param array $options
     *
     * @return \SuperClosure\ClosureParser\AbstractClosureParser
     */
    public function create(array $options = array())
    {
        // Build an options array from the default options and provided options
        $options = $options + $this->defaultOptions;
        $options = new Options($options);

        // Use the AST parser if requiring features that only the AST parser provides
        if ($options[Options::HANDLE_MAGIC_CONSTANTS] || $options[Options::HANDLE_CLASS_NAMES]) {
            $parser = new AstParser($options);
        // Otherwise, use the token parser, because it's faster
        } else {
            $parser = new TokenParser($options);
        }

        return $parser;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setDefaultOptions(array $options = array())
    {
        $this->defaultOptions = $options;

        return $this;
    }
}
