<?php

namespace Jeremeamia\SuperClosure\ClosureParser\Token;

use Jeremeamia\SuperClosure\ClosureBinding;
use Jeremeamia\SuperClosure\ClosureParser\AbstractClosureContext;

class TokenClosureContext extends AbstractClosureContext
{
    /**
     * @var array
     */
    protected $tokens;

    /**
     * @param string          $code
     * @param array           $variables
     * @param array           $tokens
     * @param ClosureBinding  $binding
     */
    public function __construct(
        $code,
        array $variables,
        array $tokens,
        ClosureBinding $binding = null
    ) {
        parent::__construct($code, $variables, $binding);
        $this->tokens = $tokens;
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
