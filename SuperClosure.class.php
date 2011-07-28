<?php

require 'FunctionParser.class.php';

/**
 * SuperClosure Class
 * 
 * The SuperClosure class encapsulates a PHP Closure and adds new capabilities
 * like serialization and code fetching. It uses the ReflectionFunction class
 * heavily to acquire information about the Closure. Because the class works
 * with Closures, it requires PHP version 5.3+. DISCLAIMERS: This class is not
 * designed to perform well due to the nature of the techniques it uses. Also, 
 * you should note that it uses the `extract()` and `eval()` functions to make
 * serialization/unserialization possible.
 * 
 * @author     Jeremy Lindblom
 * @copyright  Copyright (c) 2011 Jeremy Lindblom
 */
class SuperClosure implements Serializable
{
	protected $closure;
	protected $reflection;
	protected $code;
	protected $context;

	public function __construct(Closure $closure)
	{
		$this->_init($closure);
	}

	protected function _init(Closure $closure, $code = NULL, $context = NULL)
	{
		$this->closure = $closure;
		$this->reflection = new ReflectionFunction($closure);

		// Use the FunctionParser to get the code and context if it is not
		// provided to the initialization during an unserialization.
		if ($code === NULL OR $context === NULL)
		{
			$parser = new FunctionParser($this->reflection);

			$this->code = $parser->getCode();
			$this->context = $parser->getContext();
		}
		else
		{
			$this->code = (string) $code;
			$this->context = (array) $context;
		}
	}

	public function getClosure()
	{
		return $this->closure;
	}

	public function getReflection()
	{
		return $this->reflection;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getContext()
	{
		return $this->context;
	}

	public function __invoke()
	{
		// Delegate to the closure when this class is invoked as a method.
		// Use the fastest way depending on number of arguments.
		$args = func_get_args();
		if (count($args) == 0)
		{
			return $this->closure();
		}
		elseif (count($args) == 1)
		{
			return $this->closure($args[0]);
		}
		else
		{
			return $this->reflection->invokeArgs($args);
		}
	}

	public function __call($method, $args)
	{
		// Delegate all unknown methods to the ReflectionFunction instance.
		// Use the fastest way depending on number of arguments.
		if (count($args) == 0)
		{
			return $this->reflection->{$method}();
		}
		elseif (count($args) == 1)
		{
			return $this->reflection->{$method}($args[0]);
		}
		else
		{
			$function = array($this->reflection, $method);
			return call_user_func_array($function, $args);
		}
	}

	public function serialize()
	{
		// Closures and Reflected Closures cannot be serialized. The code and
		// context will be serialized so that the Closure can be reconstructed.
		return serialize($this->getCode(), $this->getContext());
	}

	public function unserialize($serialized)
	{
		// Unserialize the data we need to reconstruct the SuperClosure
		list($code, $context) = unserialize($serialized);

		// Simulate the original context the Closure was created in
		extract($context);

		// Eval the code to recreate the Closure
		eval("\$_closure = $code;");

		// Re-initialize the SuperClosure
		$this->_init($_closure, $code, $context);
	}
}
