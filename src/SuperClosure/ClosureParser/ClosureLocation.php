<?php

namespace SuperClosure\ClosureParser;

/**
 * Simple object for storing the location information of a closure (e.g., file, class, etc.)
 */
class ClosureLocation
{
    /** @var string|null */
    public $class;

    /** @var string|null */
    public $directory;

    /** @var string|null */
    public $file;

    /** @var string|null */
    public $function;

    /** @var string|null */
    public $line;

    /** @var string|null */
    public $method;

    /** @var string|null */
    public $namespace;

    /** @var string|null */
    public $trait;

    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if ($data) {
            foreach (array_keys(get_object_vars($this)) as $variable) {
                if (isset($data[$variable])) {
                    $this->{$variable} = $data[$variable];
                }
            }
        }
    }
}
