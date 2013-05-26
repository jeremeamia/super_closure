<?php

namespace Jeremeamia\SuperClosure;

/**
 * Parses a closure from its reflection such that the code and used (closed upon) variables are accessible. The
 * ClosureParser uses the fabulous nikic/php-parser library which creates abstract syntax trees (AST) of the code.
 *
 * @copyright Jeremy Lindblom 2010-2013
 */
class ClosureParser
{
    /**
     * @var \ReflectionFunction The reflection of the closure being parsed
     */
    protected $reflection;

    /**
     * @var \PHPParser_Node An abstract syntax tree defining the code of the closure
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
     * @param \Closure $closure
     *
     * @return ClosureParser
     */
    public static function fromClosure(\Closure $closure)
    {
        return new self(new \ReflectionFunction($closure));
    }

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
     * @return \PHPParser_Node_Expr_Closure
     * @throws \InvalidArgumentException
     */
    public function getClosureAbstractSyntaxTree()
    {
        if (!$this->abstractSyntaxTree) {
            // Setup the parser and traverser objects
            $parser = new \PHPParser_Parser(new \PHPParser_Lexer_Emulative);
            $traverser = new \PHPParser_NodeTraverser();
            $closureFinder = new ClosureFinderVisitor($this->reflection);
            $traverser->addVisitor(new \PHPParser_NodeVisitor_NameResolver);
            $traverser->addVisitor($closureFinder);

            try {
                // Parse the code from the file containing the closure and create an AST with FQCN resolved
                $statements = $parser->parse(file_get_contents($this->reflection->getFileName()));
                $traverser->traverse($statements);
            } catch (\PHPParser_Error $e) {
                throw new \InvalidArgumentException('There was an error parsing the file containing the closure.');
            }

            // Find the first closure defined in the AST that is on the line where the closure is located
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
            // Get the variable names defined in the AST
            $usedVarNames = array_map(function ($usedVar) {
                return $usedVar->var;
            }, $this->getClosureAbstractSyntaxTree()->uses);

            // Get the variable names and values using reflection
            $usedVarValues = $this->reflection->getStaticVariables();

            // Combine the two arrays to create a canonical hash of variable names and values
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
            // Use the pretty printer to print the closure code from the AST
            $printer = new \PHPParser_PrettyPrinter_Default();
            $this->code = $printer->prettyPrint(array($this->getClosureAbstractSyntaxTree()));
        }

        return $this->code;
    }
}
