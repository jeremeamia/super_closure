<?php

namespace Jeremeamia\SuperClosure\Visitor;

/**
 * Resolves scalar values (and arrays) passed through "use" statement
 *
 * @author Nikita Konstantinov
 */
final class ScalarValuesVisitor extends \PHPParser_NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $usedVariables;

    /**
     * @param \PHPParser_Node_Expr_ClosureUse[] $usedVariables
     * @throws \InvalidArgumentException
     */
    public function __construct(array $usedVariables)
    {
        $this->usedVariables = $usedVariables;
    }

    public function beforeTraverse(array $nodes)
    {
        /** @var \PHPParser_Node_Expr_Closure $closureAst */
        $closureAst = $nodes[0];

        foreach ($closureAst->uses as $use) {
            if ($use->byRef) {
                throw new \InvalidArgumentException(sprintf('Variable "%s" is passed by ref', $use->var));
            }
        }

        $closureAst->uses = array();

        foreach ($this->usedVariables as $name => $value) {
            array_unshift(
                $closureAst->stmts,
                new \PHPParser_Node_Expr_Assign(
                    new \PHPParser_Node_Expr_Variable($name),
                    $this->getNodeForValue($value)
                )
            );
        }

        return array($closureAst);
    }

    private function getNodeForValue($value)
    {
        switch (gettype($value)) {
            case 'string':
                return new \PHPParser_Node_Scalar_String($value);

            case 'integer':
                return new \PHPParser_Node_Scalar_LNumber($value);

            case 'double':
                return new \PHPParser_Node_Scalar_DNumber($value);

            case 'boolean':
                return new \PHPParser_Node_Expr_ConstFetch(
                    new \PHPParser_Node_Name_FullyQualified($value ? 'true' : 'false')
                );

            case 'NULL':
                return new \PHPParser_Node_Expr_ConstFetch(
                    new \PHPParser_Node_Name_FullyQualified('null')
                );

            case 'array':
                return new \PHPParser_Node_Expr_Array(
                    array_map(array($this, 'getNodeForValue'), $value)
                );
        }

        throw new \InvalidArgumentException('Only scalar values and arrays are allowed');
    }
} 
