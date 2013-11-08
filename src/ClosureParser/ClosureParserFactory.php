<?php

namespace Jeremeamia\SuperClosure\ClosureParser;

use Jeremeamia\SuperClosure\ClosureParser\Token\TokenParser as TurboParser;
use Jeremeamia\SuperClosure\ClosureParser\ClosureParserInterface as Parser;

class ClosureParserFactory
{
    const PARSER_CLASS = 'parser_class';
    const TURBO_MODE   = 'turbo_mode';
    const AST_PARSER   = 'ast';
    const TOKEN_PARSER = 'token';

    protected $parserClasses = array(
        self::TOKEN_PARSER => 'Jeremeamia\SuperClosure\ClosureParser\Token\TokenParser',
        self::AST_PARSER   => 'Jeremeamia\SuperClosure\ClosureParser\Ast\AstParser',
    );

    protected $defaultOptions = array(
        self::PARSER_CLASS              => null,
        self::TURBO_MODE                => false,
        Parser::HANDLE_CLOSURE_BINDINGS => true,
        Parser::HANDLE_MAGIC_CONSTANTS  => true,
        Parser::HANDLE_CLASS_NAMES      => true,
        Parser::VALIDATE_TOKENS         => true,
    );

    /**
     * @param array $options
     *
     * @return \Jeremeamia\SuperClosure\ClosureParser\AbstractClosureParser
     * @throws \InvalidArgumentException
     */
    public function create(array $options = array())
    {
        $options = $options + $this->defaultOptions;

        // Just turn on turbo mode to make it go fast
        if ($options[self::TURBO_MODE]) {
            return new TurboParser(array(
                Parser::HANDLE_CLOSURE_BINDINGS => false,
                Parser::HANDLE_MAGIC_CONSTANTS  => false,
                Parser::HANDLE_CLASS_NAMES      => false,
                Parser::VALIDATE_TOKENS         => false,
            ));
        }

        // Use a particular parser class, if specified
        if ($options[self::PARSER_CLASS]) {
            if (isset($this->parserClasses[$options[self::PARSER_CLASS]])) {
                $parserClass = $this->parserClasses[$options[self::PARSER_CLASS]];
            } elseif (class_exists($options[self::PARSER_CLASS])) {
                $parserClass = $options[self::PARSER_CLASS];
            } else {
                throw new \InvalidArgumentException('The parser class you specified does not exist.');
            }
        // Use the AST parser if requiring features that only the AST parser provides
        } elseif ($options[Parser::HANDLE_MAGIC_CONSTANTS] || $options[Parser::HANDLE_CLASS_NAMES]) {
            $parserClass = $this->parserClasses[self::AST_PARSER];
        // Otherwise, use the token parser, because it's faster
        } else {
            $parserClass = $this->parserClasses[self::TOKEN_PARSER];
        }

        return new $parserClass($options);
    }

    public function setDefaultOptions(array $options = array())
    {
        $this->defaultOptions = $options;

        return $this;
    }
}
