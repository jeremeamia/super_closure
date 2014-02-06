<?php

use SuperClosure\ClosureParser\ClosureParserInterface;
use Things\Queue;

/** @var \Closure $addTestCase */
/** @var ClosureParserInterface $astParser */
/** @var ClosureParserInterface $tokenParser */

//=============================================================================
// 1. Basic closure
//=============================================================================

$closure = function ($a, $b) {
    return $a + $b;
};

$addTestCase($closure, array(4, 7), $astParser, 11);
$addTestCase($closure, array(4, 7), $tokenParser, 11);

//=============================================================================
// 2. With a use clause
//=============================================================================

$operand = 8;
$closure = function ($a) use ($operand) {
    return $a + $operand;
};

$addTestCase($closure, array(7), $astParser, 15);
$addTestCase($closure, array(7), $tokenParser, 15);

//=============================================================================
// 3. Closure uses a closure
//=============================================================================

$otherClosure = function ($n) {return $n + 1;};
$closure = function ($n) use ($otherClosure) {
    return $otherClosure($n + 5);
};

$addTestCase($closure, array(9), $astParser, 15);
$addTestCase($closure, array(9), $tokenParser, 15);

//=============================================================================
// 4. One-liner
//=============================================================================

$c = $d = 5;
$closure = function($a,$b)use($c,$d){return$a+$b+$c+$d;};

$addTestCase($closure, array(2, 8), $astParser, 20);
$addTestCase($closure, array(2, 8), $tokenParser, 20);

//=============================================================================
// 5. Preserves magic constants
//=============================================================================

$closure = function () {
    return basename(__FILE__);
};

$addTestCase($closure, array(), $astParser, 'test-cases-php53.php');
$addTestCase($closure, array(), $tokenParser, 'SerializableClosure.php(93) : eval()\'d code', false);

//=============================================================================
// 6. Twofer
//=============================================================================

$c = $d = 5;
$closure = function($a){return$a;};function($b){return$b;};

$addTestCase($closure, array(3), $astParser, new Exception);
$addTestCase($closure, array(3), $tokenParser, new Exception);

//=============================================================================
// 7. Classes in the parameters
//=============================================================================

$closure = function (Queue $queue) {
    return iterator_to_array($queue);
};

$queue = new Queue;
$addTestCase($closure, array($queue), $astParser, array());
$addTestCase($closure, array($queue), $tokenParser, new Exception);

//=============================================================================
// 8. Classes in the body
//=============================================================================

$closure = function () {
    return Queue::IT_MODE_DELETE;
};

$addTestCase($closure, array(), $astParser, Queue::IT_MODE_DELETE);
// CANNOT TEST TOKEN PARSER BECAUSE IT CAUSES A FATAL ERROR;

//=============================================================================
// 9. Composed function
//=============================================================================

$inc = function ($n) {return $n + 1;};
$dec = function ($n) {return $n - 1;};
$compose = function ($f1, $f2) {
    return function ($n) use ($f1, $f2) {
        return $f2($f1($n));
    };
};
$closure = $compose($compose($compose($inc, $inc), $dec), $inc);

$addTestCase($closure, array(2), $astParser, 4);
$addTestCase($closure, array(2), $tokenParser, 4);

//=============================================================================
// 10. Magic constants are actually correct
//=============================================================================

$foo = new Things\Test;
$closure = $foo->getMagicClosure();

$addTestCase($closure, array(), $astParser, '[Things|Things\\Test]');
$addTestCase($closure, array(), $tokenParser, '[|]', false);
