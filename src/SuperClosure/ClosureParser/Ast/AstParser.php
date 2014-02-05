<?php

namespace SuperClosure\ClosureParser\Ast;

use SuperClosure\ClosureParser\AbstractClosureParser;
use SuperClosure\ClosureParser\ClosureParsingException;
use SuperClosure\ClosureParser\Ast\Visitor\ClosureLocatorVisitor;
use SuperClosure\ClosureParser\Ast\Visitor\MagicConstantVisitor;
use PHPParser_Node_Expr_Closure as ClosureAst;
use SuperClosure\ClosureParser\Options;

/**
 * Parses a closure from its reflection such that the code and used (closed upon) variables are accessible. The
 * ClosureParser uses the fabulous nikic/php-parser library which creates abstract syntax trees (AST) of the code.
 */
class AstParser extends AbstractClosureParser
{
    public function getDefaultOptions()
    {
        return new Options(array(
            Options::HANDLE_CLOSURE_BINDINGS => true,
            Options::HANDLE_MAGIC_CONSTANTS  => true,
            Options::HANDLE_CLASS_NAMES      => true,
        ));
    }

    public function parse($closure)
    {
        // Prepare the closure and reflection objects for parsing
        $closure = $this->prepareClosure($closure);
        $closureReflection = $closure->getReflection();

        // Find the first closure defined in the AST that is on the line where the closure is located
        $closureLocator = $this->locateClosure($closureReflection);
        if (!($closureAst = $closureLocator->getClosureNode())) {
            // @codeCoverageIgnoreStart
            throw new ClosureParsingException('The closure was not found within the abstract syntax tree.');
            // @codeCoverageIgnoreEnd
        }

        // Do a second traversal through the closure's AST to apply additional transformations
        $closureLocation = $closureLocator->getLocation();
        if ($this->options[Options::HANDLE_MAGIC_CONSTANTS]) {
            // Resolve additional nodes by making a second pass through just the closure's nodes
            $closureTraverser = new \PHPParser_NodeTraverser();
            $closureTraverser->addVisitor(new MagicConstantVisitor($closureLocation));
            $closureAst = $closureTraverser->traverse(array($closureAst));
            $closureAst = $closureAst[0];
        }

        // Get and return closure context data
        $astPrinter = new \PHPParser_PrettyPrinter_Default();
        $closureCode = $astPrinter->prettyPrint(array($closureAst));
        $closureVariables = $this->determineVariables($closureAst, $closureReflection);
        $closureBinding = $this->options[Options::HANDLE_CLOSURE_BINDINGS] ? $closure->getBinding() : null;

        return new AstClosureContext($closureCode, $closureVariables, $closureAst, $closureLocation, $closureBinding);
    }

    /**
     * Loads the PHP file, parses the code, and produces an abstract syntax tree (AST) of the code
     *
     * @param \ReflectionFunction $closureReflection
     *
     * @return ClosureLocatorVisitor
     * @throws ClosureParsingException if there is an issue while parsing the file to find the closure
     */
    private function locateClosure(\ReflectionFunction $closureReflection)
    {
        try {
            $closureLocator = new ClosureLocatorVisitor($closureReflection);
            $fileAst = $this->getFileAst($closureReflection);

            $fileTraverser = new \PHPParser_NodeTraverser();
            if ($this->options[Options::HANDLE_CLASS_NAMES]) {
                $fileTraverser->addVisitor(new \PHPParser_NodeVisitor_NameResolver);
            }
            $fileTraverser->addVisitor($closureLocator);
            $fileTraverser->traverse($fileAst);
        } catch (\PHPParser_Error $e) {
            // @codeCoverageIgnoreStart
            throw new ClosureParsingException('There was an error parsing the file containing the closure.', 0, $e);
            // @codeCoverageIgnoreEnd
        }

        return $closureLocator;
    }

    /**
     * @param \ReflectionFunction $closureReflection
     *
     * @throws ClosureParsingException
     * @return \PHPParser_Node[]
     */
    private function getFileAst(\ReflectionFunction $closureReflection)
    {
        $fileName = $closureReflection->getFileName();
        if (!file_exists($fileName)) {
            throw new ClosureParsingException("The file containing the closure, \"{$fileName}\" did not exist.");
        }

        $fileContents = file_get_contents($fileName);
        $parser = new \PHPParser_Parser(new \PHPParser_Lexer_Emulative);
        $fileAst = $parser->parse($fileContents);

        return $fileAst;
    }

    /**
     * Returns the variables that in the "use" clause of the closure definition. These are referred to as the "used
     * variables", "static variables", or "closed upon variables", "context" of the closure.
     *
     * @param ClosureAst          $closureAst
     * @param \ReflectionFunction $closureReflection
     *
     * @return array
     */
    private function determineVariables(ClosureAst $closureAst, \ReflectionFunction $closureReflection)
    {
        // Get the variable names defined in the AST
        $variableNames = array_map(function ($variableNode) {
            return $variableNode->var;
        }, $closureAst->uses);

        // Get the variable names and values using reflection
        $variableValues = $closureReflection->getStaticVariables();

        // Combine the two arrays to create a canonical hash of variable names and values
        $variables = array();
        foreach ($variableNames as $name) {
            if (isset($variableValues[$name])) {
                $variables[$name] = $variableValues[$name];
            }
        }

        return $variables;
    }
}
