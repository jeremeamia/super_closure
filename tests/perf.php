<?php

require __DIR__ . '/../vendor/autoload.php';

use SuperClosure\Serializer;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Analyzer\TokenAnalyzer;

$greeting = 'Hello';
$helloWorld = function ($name = 'World') use ($greeting) {
    echo "{$greeting}, {$name}!\n";
};

// Token
$time = microtime(true);
$serializer = new Serializer(new TokenAnalyzer);
for ($i = 0; $i < 1000; $i++) {
    $serializer->serialize($helloWorld);
}
$time = microtime(true) - $time;
echo "Token Analyzer: " . round($time, 3) . " seconds.\n";

// Token (with Signature)
$time = microtime(true);
$serializer = new Serializer(new TokenAnalyzer, '$3^28vjsdoid023ralkjs');
for ($i = 0; $i < 1000; $i++) {
    $serializer->serialize($helloWorld);
}
$time = microtime(true) - $time;
echo "Token Analyzer (with Signature): " . round($time, 3) . " seconds.\n";

// AST
$time = microtime(true);
$serializer = new Serializer(new AstAnalyzer);
for ($i = 0; $i < 1000; $i++) {
    $serializer->serialize($helloWorld);
}
$time = microtime(true) - $time;
echo "AST Analyzer: " . round($time, 3) . " seconds.\n";
