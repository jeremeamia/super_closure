<?php

require __DIR__ . '/../vendor/autoload.php';

$greeting = 'Hello';
$helloWorld = function ($name = 'World') use ($greeting) {
    echo "{$greeting}, {$name}!\n";
};

$helloWorld();
//> Hello, World!
$helloWorld('Jeremy');
//> Hello, Jeremy!

$serializer = new SuperClosure\Serializer;
$serialized = $serializer->serialize($helloWorld);
$unserialized = $serializer->unserialize($serialized);

$unserialized();
//> Hello, World!
$unserialized('Jeremy');
//> Hello, Jeremy!
