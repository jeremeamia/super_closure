<?php namespace SuperClosure\ClosureParser\Token;

use SuperClosure\ClosureParser\ClosureParser;
use SuperClosure\ClosureParser\ClosureParsingException;

/**
 * Parses a closure from its reflection such that the code and used (closed upon) variables are accessible. The
 * ClosureParser uses the fabulous nikic/php-parser library which creates abstract syntax trees (AST) of the code.
 */
class TokenParser extends ClosureParser
{
    protected static $defaultOptions = array(
        self::VALIDATE_TOKENS         => true,
        self::HANDLE_CLOSURE_BINDINGS => true,
    );

    public function parse($closure)
    {
        $closure = $this->prepareClosure($closure);
        $reflection = $closure->getReflection();
        $tokens = $this->fetchTokens($reflection);

        // Only validate the tokens if configured to do so
        if ($this->options[self::VALIDATE_TOKENS]) {
            $tokens = $this->validateTokens($tokens);
        }

        $code = implode('', $tokens);
        $variables = $this->determineVariables($reflection, $tokens);
        $binding = $this->options[self::HANDLE_CLOSURE_BINDINGS] ? $closure->getBinding() : null;

        return new TokenClosureContext($code, $variables, $tokens, $binding);
    }

    /**
     * Creates a tokenizer representing the code that is the best candidate for representing the function. It uses
     * reflection to find the file and lines of the code and then puts that code into the tokenizer.
     *
     * @param \ReflectionFunction $reflection
     *
     * @return array                   an array of token representing the closure's code.
     * @throws ClosureParsingException if the file does not exist (e.g., closure is from eval'd code)
     */
    protected function fetchTokens(\ReflectionFunction $reflection)
    {
        // Load the file containing the code for the function
        $fileName = $reflection->getFileName();
        if (!file_exists($fileName)) {
            throw new ClosureParsingException("The file containing the closure, \"{$fileName}\" did not exist.");
        }
        $file = new \SplFileObject($fileName);

        // Identify the first and last lines of the code for the function
        $firstLine = $reflection->getStartLine();
        $lastLine = $reflection->getEndLine();

        // Retrieve all of the lines that could contain code for the function
        $code = '';
        $file->seek($firstLine - 1);
        while ($file->key() < $lastLine) {
            $code .= $file->current();
            $file->next();
        }
        $code = trim($code);

        // Add a php opening tag if not already included
        if (strpos($code, '<?php') !== 0) {
            $code = "<?php\n" . $code;
        }

        // Get the tokens using the PHP tokenizer and then convert them to normalized Token objects
        /** @var Token[] $tokens */
        $tokens = array_map(function ($tokenData) {
            return Token::fromTokenData($tokenData);
        }, token_get_all($code));
        $count = count($tokens);

        // Determine which token is most likely the beginning of the closure
        $start = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->matches(T_FUNCTION)) {
                $start = $i;
                break;
            }
        }

        // Determine which token is most likely the end of the closure
        $end = 0;
        for ($i = $count - 1; $i >= 0; $i--) {
            if ($tokens[$i]->matches('}')) {
                $end = $i;
                break;
            }
        }

        // Return only the tokens
        return array_slice($tokens, $start, $end - $start + 1);
    }

    /**
     * Parses the code using the tokenizer and keeping track of matching braces.
     *
     * @param Token[] $tokens
     *
     * @return string                  The code representing the function.
     * @throws ClosureParsingException on invalid code.
     */
    protected function validateTokens(array $tokens)
    {
        $validTokens = array();
        $tokenIndex = 0;
        $braceLevel = 0;
        $parsingComplete = false;

        // Parse the code looking for the end of the function
        while (!$parsingComplete) {
            if (isset($tokens[$tokenIndex])) {
                /** @var $token Token */
                $token = $tokens[$tokenIndex];
                $tokenIndex++;
            } else {
                // The tokens have been exhausted, but the closing brace was never found. This should never happen
                // @codeCoverageIgnoreStart
                throw new ClosureParsingException('Cannot parse the closure; the code appears to be invalid.');
                // @codeCoverageIgnoreEnd
            }

            // Collect all the tokens within the function's code block
            if ($token->isOpeningBrace()) {
                $braceLevel++;
            } elseif ($token->isClosingBrace()) {
                $braceLevel--;
                if ($braceLevel === 0) {
                    $parsingComplete = true;
                }
            }

            $validTokens[] = $token;
        }

        // Ensure that there are no other functions defined, as this would indicate ambiguity in the parsed code
        if ($remainingTokens = array_slice($tokens, $tokenIndex)) {
            array_walk($remainingTokens, function (Token $token) {
                if ($token->matches(T_FUNCTION)) {
                    throw new ClosureParsingException('Multiple closures were declared on the same line of code. '
                        . 'Cannot determine which closure was the intended target.');
                }
            });
        }

        return $validTokens;
    }

    /**
     * Does some additional tokenizing and reflection to determine the names and values of variables included in the
     * closure (or context) via "use" statement. For functions that are not closures, an empty array is returned.
     *
     * @param \ReflectionFunction $reflection
     * @param Token[]             $tokens
     *
     * @return array The array of "used" variables in the closure (a.k.a the context).
     */
    protected function determineVariables(\ReflectionFunction $reflection, array $tokens)
    {
        $context = array();
        $varNames = array();
        $insideUse = false;

        // Parse the variable names from the "use" construct by scanning tokens
        foreach ($tokens as $token) {
            if (!$insideUse && $token->matches(T_USE)) {
                // Set the flag indicating that "use" construct has been reached
                $insideUse = true;
            } elseif ($insideUse && $token->matches(T_VARIABLE)) {
                // For variables found within the "use" construct, get the name
                $varNames[] = trim($token->getCode(), '$ ');
            } elseif ($insideUse && $token->isClosingParenthesis()) {
                // Finish once a closing parenthesis is encountered
                break;
            }
        }

        // Get the values of the variables that are closed upon in "use"
        $varValues = $reflection->getStaticVariables();

        // Construct the context by combining the variable names and values
        foreach ($varNames as $varName) {
            if (isset($varValues[$varName])) {
                $context[$varName] = $varValues[$varName];
            }
        }

        return $context;
    }
}
