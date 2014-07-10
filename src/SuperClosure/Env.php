<?php

namespace SuperClosure;

class Env
{
    // @codeCoverageIgnoreStart
    public static function supportsBindings()
    {
        static $supportsBindings;

        if ($supportsBindings === null) {
            $supportsBindings = method_exists('ReflectionFunction', 'getClosureThis');
        }

        return $supportsBindings;
    }
    // @codeCoverageIgnoreEnd
}
