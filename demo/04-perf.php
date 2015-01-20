<?php

require __DIR__.'/../vendor/autoload.php';

use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Analyzer\TokenAnalyzer;
use SuperClosure\Serializer;

$greeting = 'Hello';
$helloWorld = function ($name = 'World') use ($greeting) {
    echo "{$greeting}, {$name}!\n";
};

// Token
$time = microtime(true);
$serializer = new Serializer(new TokenAnalyzer());
for ($i = 0; $i < 1000; $i++) {
    $serializer->serialize($helloWorld);
}
$time = microtime(true) - $time;
echo "Token Analyzer: ".round($time, 3)." seconds.\n";

// AST
$time = microtime(true);
$serializer = new Serializer(new AstAnalyzer());
for ($i = 0; $i < 1000; $i++) {
    $serializer->serialize($helloWorld);
}
$time = microtime(true) - $time;
echo "AST Analyzer: ".round($time, 3)." seconds.\n";
