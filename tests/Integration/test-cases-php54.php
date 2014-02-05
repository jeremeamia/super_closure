<?php

use SuperClosure\SerializableClosure;

//=============================================================================
// 1. Recursive closure
//=============================================================================

$factorial1 = new SerializableClosure(function ($n) use (&$factorial1) {
    return ($n <= 1) ? 1 : $n * $factorial1($n - 1);
}, $astParser);
$factorial2 = new SerializableClosure(function ($n) use (&$factorial2) {
    return ($n <= 1) ? 1 : $n * $factorial2($n - 1);
}, $tokenParser);

$addTestCase($factorial1, array(5), $astParser, 120);
$addTestCase($factorial2, array(5), $tokenParser, 120);

//=============================================================================
// 2. Bound closure
//=============================================================================

$foo = new Foo(10);
$closure = $foo->getClosure();

$addTestCase($closure, array(), $astParser, 10);
$addTestCase($closure, array(), $tokenParser, 10);
