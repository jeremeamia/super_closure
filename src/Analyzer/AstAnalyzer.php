<?php namespace SuperClosure\Analyzer;

use SuperClosure\Exception\ClosureAnalysisException;
use SuperClosure\Analyzer\Visitor\ClosureLocatorVisitor;
use SuperClosure\Analyzer\Visitor\MagicConstantVisitor;
use PhpParser\Node\Expr\Closure as ClosureAst;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard as NodePrinter;
use PhpParser\Error as ParserError;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser as CodeParser;
use PhpParser\Lexer\Emulative as EmulativeLexer;

/**
 * Uses reflection and AST-based code parser to analyze a closure and determine
 * its code and context.
 *
 * This analyzer uses the nikic/php-parser library, and has more capabilities
 * than the token analyzer, but is, unfortunately, about 25 times slower.
 */
class AstAnalyzer implements ClosureAnalyzer
{
    public function analyze(\ReflectionFunction $reflection)
    {
        // Find the closure by traversing through a AST of the code.
        // Note: This also resolves class names to their FQCNs while traversing.
        list($ast, $location) = $this->locateClosure($reflection);

        // Make a second pass through the AST, but only through the closure's
        // nodes, to resolve any magic constants to literal values.
        $ast = $this->resolveMagicConstants($ast, $location);

        // Bounce the updated AST down to a string representation of the code.
        $code = (new NodePrinter)->prettyPrint([$ast]);

        // Use reflection and the AST to get the closure's context.
        list($context, $hasRefs) = $this->determineContext($ast, $reflection);

        return [
            'code'     => $code,
            'context'  => $context,
            'ast'      => $ast,
            'location' => $location,
            'hasRefs'  => $hasRefs,
        ];
    }

    /**
     * Parses the closure's code and produces an abstract syntax tree (AST).
     *
     * @param \ReflectionFunction $reflection
     *
     * @return ClosureLocatorVisitor
     * @throws ClosureAnalysisException if there is an issue finding the closure
     */
    private function locateClosure(\ReflectionFunction $reflection)
    {
        try {
            $locator = new ClosureLocatorVisitor($reflection);
            $fileAst = $this->getFileAst($reflection);

            $fileTraverser = new NodeTraverser;
            $fileTraverser->addVisitor(new NameResolver);
            $fileTraverser->addVisitor($locator);
            $fileTraverser->traverse($fileAst);
        } catch (ParserError $e) {
            // @codeCoverageIgnoreStart
            throw new ClosureAnalysisException(
                'There was an error analyzing the closure code.', 0, $e
            );
            // @codeCoverageIgnoreEnd
        }

        $closureAst = $locator->getClosureNode();
        if (!$closureAst) {
            // @codeCoverageIgnoreStart
            throw new ClosureAnalysisException(
                'The closure was not found within the abstract syntax tree.'
            );
            // @codeCoverageIgnoreEnd
        }

        return [$closureAst, $locator->getLocation()];
    }

    /**
     * @param \ReflectionFunction $reflection
     *
     * @throws ClosureAnalysisException
     * @return \PhpParser\Node[]
     */
    private function getFileAst(\ReflectionFunction $reflection)
    {
        $fileName = $reflection->getFileName();
        if (!file_exists($fileName)) {
            throw new ClosureAnalysisException(
                "The file containing the closure, \"{$fileName}\" did not exist."
            );
        }

        $parser = new CodeParser(new EmulativeLexer);

        return $parser->parse(file_get_contents($fileName));
    }

    /**
     * Resolves magic constants (e.g., __CLASS__) to their literal values.
     *
     * @param ClosureAst $ast
     * @param array      $location
     *
     * @return ClosureAst
     */
    private function resolveMagicConstants(ClosureAst $ast, array $location)
    {
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new MagicConstantVisitor($location));
        $ast = $traverser->traverse([$ast]);
        return $ast[0];
    }

    /**
     * Returns the variables that in the "use" clause of the closure definition.
     * These are referred to as the "used variables", "static variables", or
     * "closed upon variables", "context" of the closure.
     *
     * @param ClosureAst          $closureAst
     * @param \ReflectionFunction $reflection
     *
     * @return array
     */
    private function determineContext(
        ClosureAst $closureAst,
        \ReflectionFunction $reflection
    ) {
        // Get the variable names defined in the AST
        $refs = 0;
        $vars = array_map(function ($node) use (&$refs) {
            if ($node->byRef) {
                $refs++;
            }
            return $node->var;
        }, $closureAst->uses);

        // Get the variable names and values using reflection
        $values = $reflection->getStaticVariables();

        // Combine the names and values to create the canonical context.
        $context = [];
        foreach ($vars as $name) {
            if (isset($values[$name])) {
                $context[$name] = $values[$name];
            }
        }

        return [$context, $refs > 0];
    }
}
