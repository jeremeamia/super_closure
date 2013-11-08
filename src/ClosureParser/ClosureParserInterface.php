<?php

namespace Jeremeamia\SuperClosure\ClosureParser;

use Jeremeamia\SuperClosure\SuperClosure;

interface ClosureParserInterface
{
    const HANDLE_CLOSURE_BINDINGS = 'handle_closure_bindings';
    const HANDLE_MAGIC_CONSTANTS  = 'handle_magic_constants';
    const HANDLE_CLASS_NAMES      = 'handle_class_names';
    const VALIDATE_TOKENS         = 'validate_tokens';

    /**
     * @param \Closure|SuperClosure $closure
     *
     * @return ClosureContextInterface
     * @throws ClosureParsingException
     */
    public function parse($closure);
}
