<?php namespace SuperClosure\ClosureParser;

use SuperClosure\SuperClosureException;

/**
 * This exception is thrown when there is a problem parsing a closure
 */
class ClosureParsingException extends \RuntimeException implements SuperClosureException {}
