<?php

namespace Jeremeamia\SuperClosure;

/**
 * This is a visitor that extends the nikic/php-parser library and looks for a closure node.
 */
class ClosureFinderVisitor extends \PHPParser_NodeVisitorAbstract
{
    /**
     * @var \ReflectionFunction
     */
    protected $reflection;

    /**
     * @var \PHPParser_Node_Expr_Closure
     */
    protected $closureNode;

    /**
     * @param \ReflectionFunction $reflection
     */
    public function __construct(\ReflectionFunction $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Identifies Closure nodes and holds onto the first closure it finds that matches the line number of the closure
     * specified in the constructor by its reflection
     *
     * {@inheritdoc}
     * @throws \RuntimeException when 2 closures appear on the same line
     */
    public function leaveNode(\PHPParser_Node $node)
    {
        if ($node instanceof \PHPParser_Node_Expr_Closure) {
            $closureStartLine = $this->reflection->getStartLine();
            $nodeStartLine = $node->getAttribute('startLine');
            if ($nodeStartLine == $closureStartLine) {
                if ($this->closureNode) {
                    throw new \RuntimeException('Two closures were declared on the same line of code. '
                        . 'Cannot determine which closure to use.');
                } else {
                    $this->closureNode = $node;
                }
            }
        }
    }

    public function getClosureNode()
    {
        return $this->closureNode;
    }
}
