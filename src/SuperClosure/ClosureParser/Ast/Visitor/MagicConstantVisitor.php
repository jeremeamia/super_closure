<?php namespace SuperClosure\ClosureParser\Ast\Visitor;

use SuperClosure\ClosureParser\Ast\ClosureLocation;
use PHPParser_Node_Scalar_LNumber as NumberNode;
use PHPParser_Node_Scalar_String as StringNode;
use PHPParser_Node as AstNode;
use PHPParser_NodeVisitorAbstract as NodeVisitor;

/**
 * This is a visitor that resolves magic constants (e.g., __FILE__) to their
 * intended values within a closure's AST.
 *
 * @internal
 */
class MagicConstantVisitor extends NodeVisitor
{
    /**
     * @var array
     */
    protected $location;

    /**
     * @param \SuperClosure\ClosureParser\Ast\ClosureLocation $location
     */
    public function __construct(ClosureLocation $location)
    {
        $this->location = $location;
    }

    public function leaveNode(AstNode $node)
    {
        switch ($node->getType()) {
            case 'Scalar_LineConst' :
                return new NumberNode($node->getAttribute('startLine'));
            case 'Scalar_FileConst' :
                return new StringNode($this->location->file);
            case 'Scalar_DirConst' :
                return new StringNode($this->location->directory);
            case 'Scalar_FuncConst' :
                return new StringNode($this->location->function);
            case 'Scalar_NSConst' :
                return new StringNode($this->location->namespace);
            case 'Scalar_ClassConst' :
                return new StringNode($this->location->class);
            case 'Scalar_MethodConst' :
                return new StringNode($this->location->method);
            case 'Scalar_TraitConst' :
                return new StringNode($this->location->trait);
        }
    }
}
