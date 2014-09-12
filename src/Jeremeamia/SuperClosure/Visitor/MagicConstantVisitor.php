<?php

namespace Jeremeamia\SuperClosure\Visitor;

use Jeremeamia\SuperClosure\ClosureLocation;
use PhpParser\Node\Scalar\LNumber as NumberNode;
use PhpParser\Node\Scalar\String as StringNode;

/**
 * This is a visitor that resolves magic constants (e.g., __FILE__) to their intended values within a closure's AST
 *
 * @copyright Jeremy Lindblom 2010-2013
 */
class MagicConstantVisitor extends \PHPParser_NodeVisitorAbstract
{
    /**
     * @var ClosureLocation
     */
    protected $location;

    /**
     * @param ClosureLocation $location
     */
    public function __construct(ClosureLocation $location)
    {
        $this->location = $location;
    }

    public function leaveNode(\PHPParser_Node $node)
    {
        switch ($node->getType()) {
            case 'Scalar_MagicConst_Line' :
                return new NumberNode($node->getAttribute('startLine'));
            case 'Scalar_MagicConst_File' :
                return new StringNode($this->location->file);
            case 'Scalar_MagicConst_Dir' :
                return new StringNode($this->location->directory);
            case 'Scalar_MagicConst_Function' :
                return new StringNode($this->location->function);
            case 'Scalar_MagicConst_Namespace' :
                return new StringNode($this->location->namespace);
            case 'Scalar_MagicConst_Class' :
                return new StringNode($this->location->class);
            case 'Scalar_MagicConst_Method' :
                return new StringNode($this->location->method);
            case 'Scalar_MagicConst_Trait' :
                return new StringNode($this->location->trait);
        }
    }
}
