<?php
/**
 * SerializableClosure Class
 * 
 * Packages a PHP Closure for serialization.
 * @author     Jeremy Lindblom <http://webdevilaz.com>
 * @copyright  (c) 2010 Jeremy Lindblom
 */
class SerializableClosure implements Serializable {

	protected $closure;

	public function __construct($closure)
	{
		if ( ! is_callable($closure))
			throw new InvalidArgumentException();
		$this->closure = $closure;
	}

	public function __invoke()
	{
		$args = func_get_args();
		return call_user_func_array($this->closure, $args);
	}

	public function getClosure()
	{
		return $this->closure;
	}
	
	public function serialize()
	{
		$reflected = new ReflectionFunction($this->closure);
		if ( ! $reflected->isClosure())
			throw new RuntimeException();
		$code    = $this->_getCode($reflected);
		$context = $reflected->getStaticVariables();
		return serialize(array($code, $context));
	}
	
	public function unserialize($serialized)
	{
		list($code, $context) = unserialize($serialized);
		extract($context);
		@eval("\$_closure = $code;");
		if ( ! isset($_closure) OR ! is_callable($_closure))
			throw new RuntimeException();
		$this->closure = $_closure;
	}

	protected function _getCode($reflected)
	{
		$file = new SplFileObject($reflected->getFileName());
		$file->seek($reflected->getStartLine() - 1);
		$code = '';
		while ($file->key() < $reflected->getEndLine())
		{
			$code .= $file->current();
			$file->next();
		}
		$begin = strpos($code, 'function');
		$end   = strrpos($code, '}');
		$code  = substr($code, $begin, $end - $begin + 1);
		return $code;
	}
}