<?php namespace SuperClosure;

/**
 * Used by various other classes to help provide PHP version-specific logic.
 *
 * @internal
 */
class Env
{
    // @codeCoverageIgnoreStart
    /**
     * Determines if the current environment support Closure binding.
     *
     * @return bool
     */
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
