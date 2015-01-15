<?php

require __DIR__ . '/../vendor/autoload.php';

$serializer = new SuperClosure\Serializer(new \SuperClosure\Analyzer\TokenAnalyzer);

$factorial = function ($n) use (&$factorial) {
    return ($n <= 1) ? 1 : $n * $factorial($n - 1);
};

echo $factorial(5) . PHP_EOL;
//> 120

$serialized = $serializer->serialize($factorial);
$unserialized = $serializer->unserialize($serialized);
/** @var $unserialized callable */

echo $unserialized(5) . PHP_EOL;
//> 120

