# PHP SuperClosure – Version 2

[![Total Downloads](https://img.shields.io/packagist/dt/jeremeamia/superclosure.svg?style=flat)][1]
[![Build Status](https://img.shields.io/travis/jeremeamia/super_closure/master.svg?style=flat)][2]
[![MIT License](https://img.shields.io/packagist/l/jeremeamia/superclosure.svg?style=flat)][10]
[![Gitter](https://badges.gitter.im/Join Chat.svg)](https://gitter.im/jeremeamia/super_closure)

A PHP Library for serializing closures and anonymous functions.

## Introduction

Once upon a time, I tried to serialize a PHP `Closure` object. As you can
probably guess, it doesn't work at all. In fact, you get a very specific error
message from the PHP Runtime:

> Uncaught exception 'Exception' with message 'Serialization of 'Closure' is
> not allowed'

Even though serializing closures is "not allowed" by PHP, the SuperClosure
library makes it **possible**. Here's the way you use it:

```php
use SuperClosure\Serializer;

$serializer = new Serializer();

$greeting = 'Hello';
$hello = function ($name = 'World') use ($greeting) {
    echo "{$greeting}, {$name}!\n";
};

$hello('Jeremy');
//> Hello, Jeremy!

$serialized = $serializer->serialize($hello);
// ...
$unserialized = $serializer->unserialize($serialized);

$unserialized('Jeremy');
//> Hello, Jeremy!
```

Yep, pretty cool, right?

### Features

SuperClosure comes with two different **Closure Analyzers**, which each support
different features regarding the serialization of closures. The `TokenAnalyzer`
is not as robust as the `AstAnalyzer`, but it is around 20-25 times faster. Using
the table below, and keeping in mind what your closure's code looks like, you
should _choose the fastest analyzer that supports the features you need_.

<table>
  <thead>
    <tr>
      <th>Supported Features</th>
      <th>Via <code>AstAnalyzer</code></th>
      <th>Via <code>TokenAnalyzer</code></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        Regular closures (anonymous functions)<br>
        <code>$fn = function (...) {...};</code>
      </td>
      <td>Yes</td>
      <td>Yes</td>
    </tr>
    <tr>
      <td>
        Closures with context<br>
        <code>$fn = function () use ($a, $b, ...) {...};</code>
      </td>
      <td>Yes</td>
      <td>Yes</td>
    </tr>
    <tr>
      <td>
        Recursive closures<br>
        <code>$fn = function () use (&$fn, ...) {...};</code>
      </td>
      <td>Yes</td>
      <td>Yes</td>
    </tr>
    <tr>
      <td>
        Closures bound to an object<br>
        <code>$fn = function () {$this->something(); ...};</code>
      </td>
      <td>Yes</td>
      <td>Yes</td>
    </tr>
    <tr>
      <td>
        Closures scoped to an object<br>
        <code>$fn = function () {self::something(); ...};</code>
      </td>
      <td>Yes</td>
      <td>Yes</td>
    </tr>
    <tr>
      <td>
        Static closures (i.e, preserves the `static`-ness)<br>
        <code>$fn = static function () {...};</code>
      </td>
      <td>Yes</td>
      <td>--</td>
    </tr>
    <tr>
      <td>
        Closures with class name in params<br>
        <code>$fn = function (Foo $foo) {...};</code>
      </td>
      <td>Yes</td>
      <td>--</td>
    </tr>
    <tr>
      <td>
        Closures with class name in body<br>
        <code>$fn = function () {$foo = new Foo; ...};</code>
      </td>
      <td>Yes</td>
      <td>--</td>
    </tr>
    <tr>
      <td>
        Closures with magic constants<br>
        <code>$fn = function () {$file = __FILE__; ...};</code>
      </td>
      <td>Yes</td>
      <td>--</td>
    </tr>
    <tr>
      <td>Performance</td>
      <td><em>Slow</em></td>
      <td><em>Fast</em></td>
    </tr>
  </tbody>
</table>

### Caveats

1. For any variables used by reference (e.g., `function () use (&$vars, &$like,
  &$these) {…}`), the references are not maintained after serialization. The
  only exception to this is recursive closure references.
2. If you have two closures defined on a single line (why would you do this
  anyway?), you will not be able to serialize either one since it is ambiguous
  which closure's code should be parsed (they are _anonymous_ functions after
  all).
3. **Warning**: The `eval()` function is required to unserialize the closure.
  This functions is considered dangerous by many, so you will have to evaluate
  what precautions you may need to take when using this library. You should only
  unserialize closures retrieved from a trusted source, otherwise you are
  opening yourself up to code injection attacks. It is a good idea sign
  serialized closures if you plan on storing or transporting them. Read the
  **Signing Closures** section below for details on how to do this.
4. Cannot serialize closures that are defined within `eval()`'d code. This
  includes re-serializing a closure that has been unserialized. 

### Analyzers

You can choose the analyzer you want to use when you instantiate the
`Serializer`. If you do not specify one, the `AstAnalyzer` is used by default,
since it has the most capabilities.

```php
use SuperClosure\Serializer;
use SuperClosure\Analyzer\AstAnalyzer;
use SuperClosure\Analyzer\TokenAnalyzer;

// Use the default analyzer.
$serializer = new Serializer();

// Explicitly choose an analyzer.
$serializer = new Serializer(new AstAnalyzer());
// OR
$serializer = new Serializer(new TokenAnalyzer());
```

Analyzers are also useful on their own if you are just looking to do some
introspection on a Closure object. Check out what is returned when using the
`AstAnalyzer`:

```php
use SuperClosure\Analyzer\AstAnalyzer;

class Calculator
{
    public function getAdder($operand)
    {
        return function ($number) use ($operand) {
            return $number + $operand;
        };
    }
}

$closure = (new Calculator)->getAdder(5);
$analyzer = new AstAnalyzer();

var_dump($analyzer->analyze($closure));
// array(10) {
//   'reflection' => class ReflectionFunction#5 (1) {...}
//   'code' => string(68) "function ($number) use($operand) {
//     return $number + $operand;
// };"
//   'hasThis' => bool(false)
//   'context' => array(1) {
//     'operand' => int(5)
//   }
//   'hasRefs' => bool(false)
//   'binding' => class Calculator#2 (0) {...}
//   'scope' => string(10) "Calculator"
//   'isStatic' => bool(false)
//   'ast' => class PhpParser\Node\Expr\Closure#13 (2) {...}
//   'location' => array(8) {
//     'class' => string(11) "\Calculator"
//     'directory' => string(47) "/Users/lindblom/Projects/{...}/SuperClosureTest"
//     'file' => string(58) "/Users/lindblom/Projects/{...}/SuperClosureTest/simple.php"
//     'function' => string(9) "{closure}"
//     'line' => int(11)
//     'method' => string(22) "\Calculator::{closure}"
//     'namespace' => NULL
//     'trait' => NULL
//   }
// }
```

### Signing Closures

Version 2.1+ of SuperClosure allows you to specify a signing key, when you 
instantiate the Serializer. Doing this will configure your Serializer to
sign any closures you serialize and verify the signatures of any closures
you unserialize. Doing this can help protect you from code injection attacks
that could potentially happen if someone tampered with a serialized closure.
_Remember to keep your signing key secret_.

```php
$serializer1 = new SuperClosure\Serializer(null, $yourSecretSigningKey);
$data = $serializer1->serialize(function () {echo "Hello!\n";});
echo $data . "\n";
// %rv9zNtTArySx/1803fgk3rPS1RO4uOPPaoZfTRWp554=C:32:"SuperClosure\Serializa...

$serializer2 = new SuperClosure\Serializer(null, $incorrectKey);
try {
    $fn = $serializer2->unserialize($data);
} catch (SuperClosure\Exception\ClosureUnserializationException $e) {
    echo $e->getMessage() . "\n";
}
// The signature of the closure's data is invalid, which means the serialized
// closure has been modified and is unsafe to unserialize.
```

## Installation

To install the Super Closure library in your project using Composer, simply
require the project with Composer:

```bash
$ composer require jeremeamia/superclosure
```

You may of course manually update your require block if you so choose:

```json
{
    "require": {
        "jeremeamia/superclosure": "^2.0"
    }
}
```

Please visit the [Composer homepage][7] for more information about how to use
Composer.

## Why would I need to serialize a closure?

Well, since you are here looking at this README, you may already have a use case
in mind. Even though this concept began as an experiment, there have been some
use cases that have come up in the wild.

For example, in a [video about Laravel and IronMQ][8] by [UserScape][9], at
about the 7:50 mark they show how you can push a closure onto a queue as a job
so that it can be executed by a worker. This is nice because you do not have to
create a whole class for a job that might be really simple.

Or... you might have a dependency injection container or router object that is
built by writing closures. If you wanted to cache that, you would need to be
able to serialize it.

In general, however, serializing closures should probably be avoided.

## Tell me about how this project started

It all started  back in the beginning of 2010 when PHP 5.3 was starting to
gain traction. I set out to prove that serializing a closure could be done,
despite that PHP wouldn't let me do it. I wrote a blog post called [Extending
PHP 5.3 Closures with Serialization and Reflection][4] on my former employers'
blog, [HTMList][5], showing how it could be done. I also released the code on
GitHub.

Since then, I've made a few iterations on the code, and the most recent
iterations have been more robust, thanks to the usage of the fabulous
[nikic/php-parser][6] library.

## Who is using SuperClosure?

- [Laravel](https://github.com/laravel/framework) - Serializes a closure to potentially push onto a job queue.
- [HTTP Mock for PHP](https://github.com/InterNations/http-mock) - Serialize a closure to send to remote server within
  a test workflow.
- [Jumper](https://github.com/kakawait/Jumper) - Serialize a closure to run on remote host via SSH.
- [nicmart/Benchmark](https://github.com/nicmart/Benchmark) - Uses the `ClosureParser` to display a benchmarked
  Closure's code.
- [florianv/business](https://github.com/florianv/business) - Serializes special days to store business days definitions.
- Please let me know if and how your project uses Super Closure.

## Alternatives

This year the [Opis Closure][11] library has been introduced, that also provides
the ability to serialize a closure. You should check it out as well and see
which one suits your needs the best.

[1]:  https://packagist.org/packages/jeremeamia/superclosure
[2]:  https://travis-ci.org/jeremeamia/super_closure
[3]:  http://packagist.org/packages/jeremeamia/SuperClosure
[4]:  http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
[5]:  http://www.htmlist.com
[6]:  https://github.com/nikic/PHP-Parser
[7]:  http://getcomposer.org
[8]:  http://vimeo.com/64703617
[9]:  http://www.userscape.com
[10]: https://github.com/jeremeamia/super_closure/blob/master/LICENSE.md
[11]: https://github.com/opis/closure
