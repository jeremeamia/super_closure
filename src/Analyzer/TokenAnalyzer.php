<?php namespace SuperClosure\Analyzer;

use SuperClosure\Exception\ClosureAnalysisException;

/**
 * Uses reflection and tokenization to analyze a closure and determine its
 * code and context.
 */
class TokenAnalyzer extends ClosureAnalyzer
{
    public function determineCode(array &$data)
    {
        $data['tokens'] = $this->fetchTokens($data['reflection']);
        $data['code'] = implode('', $data['tokens']);
        $data['hasThis'] = (strpos($data['code'], '$this') !== false);
    }

    /**
     * Creates a tokenizer representing the code that is the best candidate for
     * representing the function. It uses reflection to find the file and lines
     * of the code and then puts that code into the tokenizer.
     *
     * @param \ReflectionFunction $reflection
     *
     * @return array                    tokens representing the closure's code.
     * @throws ClosureAnalysisException if the file does not exist.
     */
    private function fetchTokens(\ReflectionFunction $reflection)
    {
        // Load the file containing the code for the function.
        $fileName = $reflection->getFileName();
        if (!file_exists($fileName)) {
            throw new ClosureAnalysisException(
                "The file containing the closure, \"{$fileName}\" did not exist."
            );
        }
        $file = new \SplFileObject($fileName);

        // Identify the first and last lines of the code for the function.
        $firstLine = $reflection->getStartLine();
        $lastLine = $reflection->getEndLine();

        // Retrieve all of the lines that could contain code for the function.
        $code = '';
        $file->seek($firstLine - 1);
        while ($file->key() < $lastLine) {
            $code .= $file->current();
            $file->next();
        }
        $code = trim($code);

        // Add a php opening tag if not already included.
        if (strpos($code, '<?php') !== 0) {
            $code = "<?php\n" . $code;
        }

        // Get the PHP tokenizer's tokens and normalize them to Token objects.
        /** @var Token[] $tokens */
        $tokens = array_map(function ($tokenData) {
            return new Token($tokenData);
        }, token_get_all($code));
        $count = count($tokens);

        // Determine which token is most likely the beginning of the closure.
        $start = 0;
        for ($i = 0; $i < $count; $i++) {
            if ($tokens[$i]->matches(T_FUNCTION)) {
                $start = $i;
                break;
            }
        }

        // Determine which token is most likely the end of the closure.
        $end = 0;
        for ($i = $count - 1; $i >= 0; $i--) {
            if ($tokens[$i]->matches('}')) {
                $end = $i;
                break;
            }
        }

        $tokens = array_slice($tokens, $start, $end - $start + 1);

        return $this->validateTokens($tokens);
    }

    /**
     * Parses the code using the tokenizer and keeping track of matching braces.
     *
     * @param Token[] $tokens
     *
     * @return string                  The code representing the function.
     * @throws ClosureAnalysisException on invalid code.
     */
    private function validateTokens(array $tokens)
    {
        $validTokens = [];
        $tokenIndex = 0;
        $braceLevel = 0;
        $parsingComplete = false;

        // Parse the code looking for the end of the function.
        while (!$parsingComplete) {
            $token = $tokens[$tokenIndex++];

            // Collect all the tokens within the function's code block.
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

        // Ensure that there are no other functions defined, as this would
        // indicate ambiguity in the parsed code.
        if ($remainingTokens = array_slice($tokens, $tokenIndex)) {
            array_walk($remainingTokens, function (Token $token) {
                if ($token->matches(T_FUNCTION)) {
                    throw new ClosureAnalysisException('Multiple closures were '
                        . 'declared on the same line of code. Cannot determine '
                        . 'which closure was the intended target.');
                }
            });
        }

        return $validTokens;
    }

    protected function determineContext(array &$data)
    {
        $context = [];
        $varNames = [];
        $insideUse = false;
        $refs = 0;

        // Parse the variable names from the "use" construct by scanning tokens.
        /** @var $token Token */
        foreach ($data['tokens'] as $token) {
            if (!$insideUse && $token->matches(T_USE)) {
                // Set flag indicating that "use" construct has been reached.
                $insideUse = true;
            } elseif ($insideUse && $token->matches('&')) {
                $refs++;
            } elseif ($insideUse && $token->matches(T_VARIABLE)) {
                // For variables found within the "use" construct, get the name.
                $varNames[] = trim($token->getCode(), '$ ');
            } elseif ($insideUse && $token->isClosingParenthesis()) {
                // Finish once a closing parenthesis is encountered.
                break;
            }
        }

        // Get the values of the variables that are closed upon in "use".
        $varValues = $data['reflection']->getStaticVariables();

        // Construct the context by combining the variable names and values.
        foreach ($varNames as $varName) {
            if (isset($varValues[$varName])) {
                $context[$varName] = $varValues[$varName];
            }
        }

        $data['context'] = $context;
        $data['hasRefs'] = ($refs > 0);
    }
}
