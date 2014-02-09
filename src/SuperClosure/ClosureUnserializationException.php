<?php

namespace SuperClosure;

/**
 * This exception is thrown when there is a problem unserializing the closure
 */
class ClosureUnserializationException extends \RuntimeException implements SuperClosureException {}
