<?php

namespace Jeremeamia\SuperClosure;

use PHPParser_Error;
use PHPParser_Lexer_Emulative;
use PHPParser_Parser;
use PHPParser_PrettyPrinter_Default;
use PHPParser_Node;
use PHPParser_Node_Expr_Closure;
use PHPParser_Node_Name;
use PHPParser_NodeTraverser;
use PHPParser_NodeVisitor_NameResolver;

class ClosureParser
{
    /**
     * @var \ReflectionFunction The reflection of the closure being parsed
     */
    protected $reflection;

    /**
     * @var PHPParser_Node An abstract syntax tree defining the code of the closure
     */
    protected $abstractSyntaxTree;

    /**
     * @var array The variables used (closed upon) by the closure and their values
     */
    protected $usedVariables;

    /**
     * @var  string The closure's code
     */
    protected $code;

    /**
     * @param \ReflectionFunction $reflection
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\ReflectionFunction $reflection)
    {
        if (!$reflection->isClosure()) {
            throw new \InvalidArgumentException('You must provide the reflection of a closure.');
        }

        $this->reflection = $reflection;
    }

    /**
     * @return \ReflectionFunction
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * @return PHPParser_Node_Expr_Closure
     * @throws \InvalidArgumentException
     */
    public function getClosureAbstractSyntaxTree()
    {
        if (!$this->abstractSyntaxTree) {
            $parser = new PHPParser_Parser(new PHPParser_Lexer_Emulative);
            $traverser = new PHPParser_NodeTraverser();
            $closureFinder = new ClosureFinderVisitor($this->reflection);
            $traverser->addVisitor(new PHPParser_NodeVisitor_NameResolver);
            $traverser->addVisitor($closureFinder);

            try {
                // Use the PHP parser and lexer to get an AST of the file containing the closure
                $statements = $parser->parse(file_get_contents($this->reflection->getFileName()));
                $traverser->traverse($statements);
            } catch (PHPParser_Error $e) {
                throw new \InvalidArgumentException('There was an error parsing the file containing the closure.');
            }

            // Find only the first closure in the AST on the line where the closure is located
            $this->abstractSyntaxTree = $closureFinder->getClosureNode();
            if (!$this->abstractSyntaxTree) {
                throw new \InvalidArgumentException('The closure was not found within the abstract syntax tree.');
            }
        }

        return $this->abstractSyntaxTree;
    }

    /**
     * @return array
     */
    public function getUsedVariables()
    {
        if (!$this->usedVariables) {
            $usedVarNames = array_map(function ($usedVar) {
                return $usedVar->var;
            }, $this->getClosureAbstractSyntaxTree()->uses);

            $usedVarValues = $this->reflection->getStaticVariables();

            $this->usedVariables = array();
            foreach ($usedVarNames as $name) {
                if (isset($usedVarValues[$name])) {
                    $this->usedVariables[$name] = $usedVarValues[$name];
                }
            }
        }

        return $this->usedVariables;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        if (!$this->code) {
            $printer = new PHPParser_PrettyPrinter_Default();
            $this->code = $printer->prettyPrint(array($this->getClosureAbstractSyntaxTree()));
        }

        return $this->code;
    }
}
