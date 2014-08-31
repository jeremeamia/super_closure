<?php namespace SuperClosure\ClosureParser\Ast\Visitor;

use SuperClosure\ClosureParser\Ast\ClosureLocation;
use PhpParser\Node\Scalar\LNumber as NumberNode;
use PhpParser\Node\Scalar\String as StringNode;
use PhpParser\Node as AstNode;
use PhpParser\NodeVisitorAbstract as NodeVisitor;

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
            case 'Scalar\MagicConst\Class_' :
                return new StringNode($this->location->class);
            case 'Scalar\MagicConst\Dir' :
                return new StringNode($this->location->directory);
            case 'Scalar\MagicConst\File' :
                return new StringNode($this->location->file);
            case 'Scalar\MagicConst\Function_' :
                return new StringNode($this->location->function);
            case 'Scalar\MagicConst\Line' :
                return new NumberNode($node->getAttribute('startLine'));
            case 'Scalar\MagicConst\Method' :
                return new StringNode($this->location->method);
            case 'Scalar\MagicConst\Namespace_' :
                return new StringNode($this->location->namespace);
            case 'Scalar\MagicConst\Trait_' :
                return new StringNode($this->location->trait);
        }
    }
}
