<?php

require __DIR__ . '/../vendor/autoload.php';

use Jeremeamia\SuperClosure\SerializableClosure;

$foo = 2;
$closure = function ($bar) use ($foo) {
    return $foo + $bar;
};

$closure = new SerializableClosure($closure);

echo "EVALUATION:\n";
echo $closure(8) . "\n\n";

$serialized = serialize($closure);

echo "SERIALIZATION:\n";
var_dump($serialized);
echo "\n";

/** @var $unserialized \Closure */
$unserialized = unserialize($serialized);

echo "POST-SERIALIZATION EVALUATION:\n";
echo $unserialized(8) . "\n\n";
