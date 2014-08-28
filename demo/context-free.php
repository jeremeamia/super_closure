<?php

require __DIR__ . '/../vendor/autoload.php';

use Jeremeamia\SuperClosure\ClosureParser;

$a = 42 * 2;
$b = null;
$c = ['hey', 123];
$d = false;

$parser = ClosureParser::fromClosure(function() use ($a, $b, $c, $d) {
    return 42;
});

echo $parser->getContextFreeCode() . PHP_EOL;
