<?php namespace SuperClosure\ClosureParser\Ast;

/**
 * Simple object for storing the location of a closure (e.g., file, class, etc.)
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
        static $vars = array(
            'class',
            'directory',
            'file',
            'function',
            'line',
            'method',
            'namespace',
            'trait',
        );

        if ($data) {
            foreach ($vars as $var) {
                if (isset($data[$var])) {
                    $this->{$var} = $data[$var];
                }
            }
        }
    }
}
