<?php
/**
 * FunctionParser Class
 *
 * @author     Jeremy Lindblom
 * @copyright  Copyright (c) 2011 Jeremy Lindblom
 */
class FunctionParser
{
	protected $reflection;
	protected $tokens;
	protected $code;
	protected $context;

	public function __construct(ReflectionFunctionAbstract $reflection)
	{
		$this->reflection = $reflection;
		$this->tokens = $this->_prepareTokens();
		$this->code = $this->_parseCode();
		$this->context = $this->_parseContext();
	}

	public function getReflection()
	{
		return $this->reflection;
	}

	public function getTokens()
	{
		return $this->tokens;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getContext()
	{
		return $this->context;
	}

	protected function _prepareTokens()
	{
		// Load the file containing the code for the Closure
		$file = new SplFileObject($this->reflection->getFileName());

		// Identify the first and last lines of the code for the Closure
		$first_line = $this->reflection->getStartLine();
		$last_line = $this->reflection->getEndLine();

		// Retrieve all of the lines that contain code for the Closure
		$code = '';
		$file->seek($first_line - 1);
		while ($file->key() < $last_line)
		{
			$code .= $file->current();
			$file->next();
		}

		// Eliminate code that is (for sure) not a part of the Closure
		$beginning = strpos($code, 'function');
		$ending = strrpos($code, '}');
		$code = trim(substr($code, $beginning, $ending - $beginning + 1));

		// Tokenize the remaining code using PHP's lexical scanner
		$tokens = token_get_all("<?php\n".$code);
		array_shift($tokens);

		// Make sure only one function is defined in this code block
		$functions_defined = array_filter($tokens, function($token) {
			return (is_array($token) AND $token[0] == T_FUNCTION);
		});
		if (count($functions_defined) > 1)
		{
			throw new RuntimeException('Cannot parse the closure; too many '
			. 'functions declared in the code block containing the closure.');
		}

		return $tokens;
	}

	protected function _parseCode()
	{
		// Parse the code, looking for the end of the function
		$brace_level = 0;
		$parsed_code = '';
		$parsing_complete = FALSE;
		foreach ($this->tokens as $token)
		{
			//if (is_array($token)) $token[0] = token_name($token[0]);
			//echo '<pre>'.htmlentities(print_r($token, 1)).'</pre>';
			if (is_array($token))
			{
				// Only need the actual PHP code of the token
				$token = $token[1];
			}
			elseif ($token === '{')
			{
				// Keep track of nested opening braces
				$brace_level++;
			}
			elseif ($token === '}')
			{
				// Keep track of nested closing braces
				$brace_level--;

				// Once we reach the function's closing brace, mark as complete
				if ($brace_level === 0)
				{
					$parsing_complete = TRUE;
				}
			}

			// Reconstruct the code token by token
			$parsed_code .= $token;

			// Once the closing brace is found, stop parsing
			if ($parsing_complete)
			{
				break;
			}
		}

		// If all tokens have been looked at and the closing brace was not 
		// found, then there is a problem with the code defining the Closure.
		if ( ! $parsing_complete)
		{
			throw new RuntimeException('Cannot parse the Closure. The code '
			. 'defining the Closure was found to be invalid.');
		}

		return $parsed_code;
	}

	protected function _parseContext()
	{
		// Parse the variable names from the "use" contruct by scanning tokens
		$variable_names = array();
		$inside_use_construct = FALSE;
		foreach ($this->tokens as $token)
		{
			if (is_array($token))
			{
				if ($token[0] === T_USE)
				{
					// Once we find the "use" construct, set the flag
					$inside_use_construct = TRUE;
				}
				elseif ($inside_use_construct AND $token[0] == T_VARIABLE)
				{
					// For variables found in the "use" construct, get the name
					$variable_names[] = trim($token[1], '$ ');
				}
			}
			elseif ($inside_use_construct AND $token === ')')
			{
				// Once we encounter a closing parenthesis at the end of the
				// "use" construct, then we are finished parsing.
				break;
			}
		}

		// Get the values of the variables that are closed upon in "use"
		$variable_values = $this->reflection->getStaticVariables();

		// The context consists only of the items in both variable arrays
		$context = array();
		foreach ($variable_names as $variable_name)
		{
			$context[$variable_name] = $variable_values[$variable_name];
		}

		return $context;
	}
}
