<?php

require __DIR__ . '/../vendor/autoload.php';

use SuperClosure\SerializableClosure;

$factorial = new SerializableClosure(function ($n) use (&$factorial) {
    return ($n <= 1) ? 1 : $n * $factorial($n - 1);
});

echo $factorial(5) . PHP_EOL;
//> 120

$serialized = serialize($factorial);
$unserialized = unserialize($serialized);
/** @var $unserialized callable */

echo $unserialized(5) . PHP_EOL;
//> 120

