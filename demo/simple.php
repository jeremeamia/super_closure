<?php

const DS = DIRECTORY_SEPARATOR;

require_once dirname(__DIR__) . DS . 'vendor' . DS . '.composer' . DS . 'autoload.php';

use SuperClosure\SuperClosure;

$foo = 2;
$closure = function($bar) use($foo) {
    return $foo + $bar;
};

$super_closure = new SuperClosure($closure);

echo "EVALUATION:" . PHP_EOL;
echo $super_closure(8) . PHP_EOL;
echo PHP_EOL;

echo "CODE:" . PHP_EOL;
echo $super_closure->getCode() . PHP_EOL;
echo PHP_EOL;

echo 'PARAMETERS:' . PHP_EOL;
var_dump($super_closure->getParameters());
echo PHP_EOL;

echo 'CONTEXT:' . PHP_EOL;
var_dump($super_closure->getContext());
echo PHP_EOL;

$serialized = serialize($super_closure);

echo 'SERIALIZATION:' . PHP_EOL;
var_dump($serialized);
echo PHP_EOL;

$unserialized = unserialize($serialized);

echo "POST-SERIALIZATION EVALUATION:" . PHP_EOL;
echo $super_closure(8) . PHP_EOL;
echo PHP_EOL;
