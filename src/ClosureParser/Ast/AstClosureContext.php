<?php

namespace Jeremeamia\SuperClosure\ClosureParser\Ast;

use Jeremeamia\SuperClosure\ClosureBinding;
use Jeremeamia\SuperClosure\ClosureParser\AbstractClosureContext;
use Jeremeamia\SuperClosure\ClosureParser\ClosureLocation;
use PHPParser_Node_Expr_Closure as ClosureAst;

class AstClosureContext extends AbstractClosureContext
{
    /**
     * @var ClosureAst
     */
    protected $ast;

    /**
     * @var ClosureLocation
     */
    protected $location;

    /**
     * @param string          $code
     * @param array           $variables
     * @param ClosureAst      $ast
     * @param ClosureLocation $location
     * @param ClosureBinding  $binding
     */
    public function __construct(
        $code,
        array $variables,
        ClosureAst $ast,
        ClosureLocation $location,
        ClosureBinding $binding = null
    ) {
        parent::__construct($code, $variables, $binding);
        $this->ast = $ast;
        $this->location = $location;
    }

    /**
     * @return ClosureAst
     */
    public function getAst()
    {
        return $this->ast;
    }

    /**
     * @return ClosureLocation
     */
    public function getLocation()
    {
        return $this->location;
    }
}
