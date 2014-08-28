<?php

namespace Jeremeamia\SuperClosure\Visitor;

/**
 * Detects if (closure's) AST contains $this variable
 *
 * @author Nikita Konstantinov
 */
final class ThisVariableDetectorVisitor extends \PHPParser_NodeVisitorAbstract
{
    private $detected = false;

    public function leaveNode(\PHPParser_Node $node)
    {
        if ($node instanceof \PHPParser_Node_Expr_Variable) {
            if ($node->name === 'this') {
                $this->detected = true;
            }
        }
    }

    public function wasDetected()
    {
        return $this->detected;
    }
}
