<?php

namespace Jeremeamia\SuperClosure;

use PHPParser_NodeVisitorAbstract;
use PHPParser_Node;
use PHPParser_Node_Expr_Closure;

class ClosureFinderVisitor extends PHPParser_NodeVisitorAbstract
{
    protected $reflection;

    protected $closureNode;

    public function __construct(\ReflectionFunction $reflection)
    {
        $this->reflection = $reflection;
    }

    public function leaveNode(PHPParser_Node $node)
    {
        if (!$this->closureNode && $node instanceof PHPParser_Node_Expr_Closure) {
            $nodeStartLine = $node->getAttribute('startLine');
            $closureStartLine = $this->reflection->getStartLine();
            if ($nodeStartLine == $closureStartLine) {
                $this->closureNode = $node;
            }
        }
    }

    public function getClosureNode()
    {
        return $this->closureNode;
    }
}
