<?php

namespace SuperClosure\ClosureParser\Ast;

use SuperClosure\ClosureParser\ClosureParser;
use SuperClosure\ClosureParser\ClosureParsingException;
use SuperClosure\ClosureParser\Ast\Visitor\ClosureLocatorVisitor;
use SuperClosure\ClosureParser\Ast\Visitor\MagicConstantVisitor;
use PHPParser_Node_Expr_Closure as ClosureAst;

/**
 * Parses a closure from its reflection such that the code and used (closed upon) variables are accessible. The
 * ClosureParser uses the fabulous nikic/php-parser library which creates abstract syntax trees (AST) of the code.
 */
class AstParser extends ClosureParser
{
    protected static $defaultOptions = array(
        self::HANDLE_CLOSURE_BINDINGS => true,
        self::HANDLE_MAGIC_CONSTANTS  => true,
        self::HANDLE_CLASS_NAMES      => true,
    );

    public function parse($closure)
    {
        // Prepare the closure and reflection objects for parsing
        $closure = $this->prepareClosure($closure);
        $reflection = $closure->getReflection();

        // Find the closure defined in the AST
        $locator = $this->locateClosure($reflection);
        if (!($ast = $locator->getClosureNode())) {
            // @codeCoverageIgnoreStart
            throw new ClosureParsingException('The closure was not found within the abstract syntax tree.');
            // @codeCoverageIgnoreEnd
        }

        // Do a second traversal through the closure's AST to apply additional transformations
        $location = $locator->getLocation();
        if ($this->options[self::HANDLE_MAGIC_CONSTANTS]) {
            // Resolve additional nodes by making a second pass through just the closure's nodes
            $traverser = new \PHPParser_NodeTraverser();
            $traverser->addVisitor(new MagicConstantVisitor($location));
            $ast = $traverser->traverse(array($ast));
            $ast = $ast[0];
        }

        // Get and return closure context data
        $printer = new \PHPParser_PrettyPrinter_Default();
        $code = $printer->prettyPrint(array($ast));
        $variables = $this->determineVariables($ast, $reflection);
        $binding = $this->options[self::HANDLE_CLOSURE_BINDINGS] ? $closure->getBinding() : null;

        return new AstClosureContext($code, $variables, $ast, $location, $binding);
    }

    /**
     * Loads the PHP file, parses the code, and produces an abstract syntax tree (AST) of the code
     *
     * @param \ReflectionFunction $reflection
     *
     * @return ClosureLocatorVisitor
     * @throws ClosureParsingException if there is an issue while parsing the file to find the closure
     */
    private function locateClosure(\ReflectionFunction $reflection)
    {
        try {
            $locator = new ClosureLocatorVisitor($reflection);
            $fileAst = $this->getFileAst($reflection);

            $fileTraverser = new \PHPParser_NodeTraverser();
            if ($this->options[self::HANDLE_CLASS_NAMES]) {
                $fileTraverser->addVisitor(new \PHPParser_NodeVisitor_NameResolver);
            }
            $fileTraverser->addVisitor($locator);
            $fileTraverser->traverse($fileAst);
        } catch (\PHPParser_Error $e) {
            // @codeCoverageIgnoreStart
            throw new ClosureParsingException('There was an error parsing the file containing the closure.', 0, $e);
            // @codeCoverageIgnoreEnd
        }

        return $locator;
    }

    /**
     * @param \ReflectionFunction $reflection
     *
     * @throws ClosureParsingException
     * @return \PHPParser_Node[]
     */
    private function getFileAst(\ReflectionFunction $reflection)
    {
        $fileName = $reflection->getFileName();
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
     * @param \ReflectionFunction $reflection
     *
     * @return array
     */
    private function determineVariables(ClosureAst $closureAst, \ReflectionFunction $reflection)
    {
        // Get the variable names defined in the AST
        $varNames = array_map(function ($varNode) {
            return $varNode->var;
        }, $closureAst->uses);

        // Get the variable names and values using reflection
        $varValues = $reflection->getStaticVariables();

        // Combine the names and values to create the canonical set of variables
        $vars = array();
        foreach ($varNames as $name) {
            if (isset($varValues[$name])) {
                $vars[$name] = $varValues[$name];
            }
        }

        return $vars;
    }
}
