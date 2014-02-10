<?php

namespace SuperClosure\ClosureParser\Token;

use SuperClosure\ClosureBinding;
use SuperClosure\ClosureParser\AbstractClosureContext;

class TokenClosureContext extends AbstractClosureContext
{
    /**
     * @var Token[]
     */
    protected $tokens;

    /**
     * @param string          $code
     * @param array           $variables
     * @param Token[]         $tokens
     * @param ClosureBinding  $binding
     *
     * @throws \InvalidArgumentException if the tokens array is not an array of only Token objects
     */
    public function __construct(
        $code,
        array $variables,
        array $tokens,
        ClosureBinding $binding = null
    ) {
        // Validate tokens array
        array_walk($tokens, function ($value) {
            if (!$value instanceof Token) {
                throw new \InvalidArgumentException('The tokens array must consist of Token objects.');
            }
        });

        // Construct the context
        parent::__construct($code, $variables, $binding);
        $this->tokens = $tokens;
    }

    /**
     * @return Token[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
