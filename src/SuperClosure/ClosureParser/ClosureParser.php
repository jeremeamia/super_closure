<?php namespace SuperClosure\ClosureParser;

use SuperClosure\ClosureParser\Ast\AstParser;
use SuperClosure\ClosureParser\Token\TokenParser;
use SuperClosure\SuperClosure;

abstract class ClosureParser
{
    const HANDLE_CLASS_NAMES      = 'handle_class_names';
    const HANDLE_CLOSURE_BINDINGS = 'handle_closure_bindings';
    const HANDLE_MAGIC_CONSTANTS  = 'handle_magic_constants';
    const VALIDATE_TOKENS         = 'validate_tokens';

    /**
     * @var array
     */
    protected static $defaultOptions = array(
        self::HANDLE_CLOSURE_BINDINGS => true,
        self::HANDLE_MAGIC_CONSTANTS  => true,
        self::HANDLE_CLASS_NAMES      => true,
        self::VALIDATE_TOKENS         => true,
    );

    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     *
     * @return ClosureParser
     */
    public static function create(array $options = array())
    {
        // Build an options array from the default options and provided options
        $options += self::$defaultOptions;

        // Use the AST parser if requiring features that only the AST parser provides
        if ($options[self::HANDLE_MAGIC_CONSTANTS] || $options[self::HANDLE_CLASS_NAMES]) {
            $parser = new AstParser($options);
        } else {
            // Otherwise, use the token parser, because it's faster
            $parser = new TokenParser($options);
        }

        return $parser;
    }

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + static::$defaultOptions;
    }

    /**
     * @param \Closure|SuperClosure $closure
     *
     * @return ClosureContext
     * @throws ClosureParsingException
     */
    abstract public function parse($closure);

    /**
     * @param \Closure|SuperClosure $closure
     *
     * @return SuperClosure
     * @throws \InvalidArgumentException
     */
    protected function prepareClosure($closure)
    {
        if ($closure instanceof \Closure) {
            return new SuperClosure($closure);
        }

        if ($closure instanceof SuperClosure) {
            return $closure;
        }

        throw new \InvalidArgumentException('You must provide a PHP Closure or SuperClosure object to be parsed.');
    }
}
