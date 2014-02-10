<?php

namespace SuperClosure\ClosureParser;

class Options extends \ArrayObject
{
    const HANDLE_CLASS_NAMES      = 'handle_class_names';
    const HANDLE_CLOSURE_BINDINGS = 'handle_closure_bindings';
    const HANDLE_MAGIC_CONSTANTS  = 'handle_magic_constants';
    const VALIDATE_TOKENS         = 'validate_tokens';

    /**
     * @param array $options
     *
     * @return Options
     */
    public static function fromDefaults(array $options = array())
    {
        return new self($options + array(
            self::HANDLE_CLOSURE_BINDINGS => true,
            self::HANDLE_MAGIC_CONSTANTS  => true,
            self::HANDLE_CLASS_NAMES      => true,
            self::VALIDATE_TOKENS         => true,
        ));
    }
}
