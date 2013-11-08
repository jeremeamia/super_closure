<?php

namespace Jeremeamia\SuperClosure\ClosureParser;

/**
 * Simple object for storing the location information of a closure (e.g., file, class, etc.)
 */
class ClosureLocation
{
    /** @var string */
    public $class;

    /** @var string */
    public $directory;

    /** @var string */
    public $file;

    /** @var string */
    public $function;

    /** @var string */
    public $line;

    /** @var string */
    public $method;

    /** @var string */
    public $namespace;

    /** @var string */
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
