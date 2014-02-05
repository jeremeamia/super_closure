<?php

namespace SuperClosure\ClosureParser;

class Options implements \ArrayAccess
{
    const HANDLE_CLASS_NAMES      = 'handle_class_names';
    const HANDLE_CLOSURE_BINDINGS = 'handle_closure_bindings';
    const HANDLE_MAGIC_CONSTANTS  = 'handle_magic_constants';
    const VALIDATE_TOKENS         = 'validate_tokens';
    const TURBO_MODE              = 'turbo_mode';

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
     * @var array The internal array holding the options
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options + self::$defaultOptions;
    }

    /**
     * @param Options $options
     */
    public function merge(Options $options)
    {
        $this->options = $options->toArray() + $this->options;
    }

    public function offsetExists($key)
    {
        return isset($this->options[$key]);
    }

    public function offsetGet($key)
    {
        return isset($this->options[$key]) ? $this->options[$key] : null;
    }

    public function offsetSet($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function offsetUnset($key)
    {
        $this->options[$key] = null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->options;
    }
}
