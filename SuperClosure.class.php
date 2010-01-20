<?php
/**
 * Super Closure Class
 * 
 * The SuperClosure class encapsulates a PHP Closure and adds new capabilities
 * like serialization and code retrieval. It uses the ReflectionFunction class
 * heavily to acquire information about the closure.
 * @author		Jeremy Lindblom
 * @copyright	(c) 2010 Synapse Studios, LLC.
 */
class SuperClosure {

	protected $closure = NULL;
	protected $reflection = NULL;
	protected $code = NULL;
	protected $used_variables = array();


	public function __construct($function)
	{
		if ( ! $function instanceOf Closure)
			throw new InvalidArgumentException();

		$this->closure = $function;
		$this->reflection = new ReflectionFunction($function);
		$this->code = $this->_fetchCode();
		$this->used_variables = $this->_fetchUsedVariables();
	}


	public function __invoke()
	{
		$args = func_get_args();
		return $this->reflection->invokeArgs($args);
	}


	public function getClosure()
	{
		return $this->closure;
	}


	protected function _fetchCode()
	{
		$code = '';
		$file = new SplFileObject($this->reflection->getFileName());
		$file->seek($this->reflection->getStartLine()-1);
		while ($file->key() < $this->reflection->getEndLine())
		{
			$code .= $file->current();
			$file->next();
		}

		$begin = strpos($code, 'function');
		$end = strrpos($code, '}');
		$code = substr($code, $begin, $end - $begin + 1);

		return $code;
	}


	public function getCode()
	{
		return $this->code;
	}


	public function getParameters()
	{
		return $this->reflection->getParameters();
	}


	protected function _fetchUsedVariables()
	{
		$use_index = stripos($this->code, 'use');
		if ( ! $use_index)
			return array();

		$begin = strpos($this->code, '(', $use_index) + 1;
		$end = strpos($this->code, ')', $begin);

		$static_vars = $this->reflection->getStaticVariables();
		$vars = explode(',', substr($this->code, $begin, $end - $begin));

		$used_vars = array();
		foreach ($vars as $var)
		{
			$var = trim($var, ' $&');
			$used_vars[$var] = $static_vars[$var];
		}

		return $used_vars;
	}


	public function getUsedVariables()
	{
		return $this->used_variables;
	}


	public function __sleep()
	{
		return array('code', 'used_variables');
	}


	public function __wakeup()
	{
		extract($this->used_variables);

		eval('$_function = '.$this->code.';');
		if (isset($_function) AND $_function instanceOf Closure)
		{
			$this->closure = $_function;
			$this->reflection = new ReflectionFunction($_function);
		}
		else
			throw new Exception();
	}

}