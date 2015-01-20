<?php

namespace SuperClosure\Exception;

use RuntimeException;

/**
 * This exception is thrown when there is a problem unserializing a closure.
 */
class ClosureUnserializationException extends RuntimeException implements SuperClosureException
{
    //
}
