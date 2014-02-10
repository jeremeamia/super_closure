<?php

namespace SuperClosure\ClosureParser\Token;

/**
 * The Token object is an object-oriented abstraction representing a single item from the results of the get_token_all()
 * function, which is part of PHP tokenizer, or lexical scanner. There are also many convenience methods revolved around
 * the token's identity.
 *
 * @link http://us2.php.net/manual/en/tokens.php
 */
class Token
{
    /**
     * @var string The token name.
     */
    protected $name;

    /**
     * @var int|null The token's integer value.
     */
    protected $value;

    /**
     * @var string The parsed code of the token.
     */
    protected $code;

    /**
     * @var int|null The line number of the token in the original code.
     */
    protected $line;

    /**
     * @param array|string $tokenData
     *
     * @return Token
     * @throws \InvalidArgumentException
     */
    public static function fromTokenData($tokenData)
    {
        if (is_string($tokenData)) {
            return new Token($tokenData);
        } elseif (is_array($tokenData)) {
            $tokenData = array_pad($tokenData, 3, null);
            return new Token($tokenData[1], $tokenData[0], $tokenData[2]);
        }

        throw new \InvalidArgumentException('The token was invalid.');
    }

    /**
     * Constructs a token object.
     *
     * @param string   $code
     * @param int|null $value
     * @param int|null $line
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($code, $value = null, $line = null)
    {
        if ($line && !is_int($line)) {
            throw new \InvalidArgumentException('The line number must be an integer or null.');
        }

        if ($value && !is_int($value)) {
            throw new \InvalidArgumentException('The token value must be an integer or null.');
        }

        $this->code = $code;
        $this->value = $value;
        $this->line = $line;
        $this->name = $value ? token_name($value) : null;
    }

    /**
     * Get the token name.
     *
     * @return string The token name. Always null for literal tokens.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the token's integer value. Always null for literal tokens.
     *
     * @return int|null The token value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the token's PHP code as a string.
     *
     * @return string The token code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the line where the token was defined. Always null for literal tokens.
     *
     * @return int|null The line number.
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * Determines whether the token is an opening brace.
     *
     * @return bool True if the token is an opening brace.
     */
    public function isOpeningBrace()
    {
        return ($this->code === '{' || $this->name === 'T_CURLY_OPEN' || $this->name === 'T_DOLLAR_OPEN_CURLY_BRACES');
    }

    /**
     * Determines whether the token is an closing brace.
     *
     * @return bool True if the token is an closing brace.
     */
    public function isClosingBrace()
    {
        return ($this->code === '}');
    }

    /**
     * Determines whether the token is an opening parenthesis.
     *
     * @return bool True if the token is an opening parenthesis.
     */
    public function isOpeningParenthesis()
    {
        return ($this->code === '(');
    }

    /**
     * Determines whether the token is an closing parenthesis.
     *
     * @return bool True if the token is an closing parenthesis.
     */
    public function isClosingParenthesis()
    {
        return ($this->code === ')');
    }

    /**
     * Determines whether the token's integer value or code is equal to the specified value.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the token is equal to the value.
     */
    public function matches($value)
    {
        return ($this->code === $value || $this->value === $value);
    }

    public function __toString()
    {
        return $this->code;
    }
}
