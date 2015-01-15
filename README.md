# PHP Super Closure

Once upon a time, I tried to serialize a PHP `Closure` object. As you can
probably guess, it doesn't work at all. In fact, you get a very specific error
message from your friendly, neighborhood PHP Runtime:

> Uncaught exception 'Exception' with message 'Serialization of 'Closure' is
> not allowed'

However, even though it is not "allowed" by PHP, the SuperClosure library—
[jeremeamia/superclosure][3] on Packagist—makes it **possible** to circumvent
this seemingly arbitrary limitation.

```php
require 'vendor/autoload.php';

$serializer = new SuperClosure\Serializer()

$greeting = 'Hello';
$hello = function ($name = 'World') use ($greeting) {
    echo "{$greeting}, {$name}!\n";
};

$hello();
//> Hello, World!
$hello('Jeremy');
//> Hello, Jeremy!

$serialized = $serializer->serialize($helloWorld);
$unserialized = $serializer->unserialize($serialized);

$unserialized();
//> Hello, World!
$unserialized('Jeremy');
//> Hello, Jeremy!
```

Yep, pretty cool, huh?

## Tell Me More!

It all started way back in the beginning of 2010 when PHP 5.3 was starting to
gain traction. I wrote a blog post called [Extending PHP 5.3 Closures with
Serialization and Reflection][4] on my former employers' blog, [HTMList][5],
showing how it can be done. Since then I've made a few iterations on the code,
and this most recent iteration brings with it a generally more robust solution
that takes advantage of the fabulous [nikic/php-parser][6] library.

### Features

* Grants the ability to serialize closures
* Handles closures with context (i.e., that have variables in the `use`
  statement)
* Handles closures with a binding (i.e., that reference `$this` or `self` in the
  function body)
* Handles recursive closures… _yeah, really_
* Allows you to get the code, context, and binding of a closure
* Offers 2 techniques for analyzing the closure's code.
    1. **Abstract syntax tree (AST)**
        * Converts class names to fully-qualified class names (FQCNs) before
          serialization, so the closure references the correct objects where it
          is unserialized.
        * Replaces magic constants with their actual values so that the closure
          behaves as expected after unserialization.
    2. **Tokenization**
        * 25 times faster than the AST analyzer, but does not offer the above
          two features.
* PSR-4 compliant and installable via Composer

### Caveats

1. For any variables used by reference (e.g., `function () use (&$vars, &$like,
   &$these) {…}`), the references are not maintained after serialization.
2. If you have two closures defined on a single line (you shouldn't do this
   anyway), you will not be able to serialize either one since it is ambiguous
   which closure's code should be parsed.
3. **Warning**: The `eval()` function is required to unserialize the closure.
   This functions is considered dangerous by many, so you will have to evaluate
   what precautions you may need to take when using this library. Unfortunately,
   `eval()` *must* be used to make this library work; there is no other way.

## Installation

To install the Super Closure library in your project using Composer, first add
the following to your `composer.json` config file.
```json
{
    "require": {
        "jeremeamia/superclosure": "~2.0"
    }
}
```
Then run Composer's install or update commands to complete installation. Please
visit the [Composer homepage][7] for more information about how to use Composer.

## Why Would I Need To Serialize Closures?

Well, since you are here looking at this README, you may already have a use case
in mind. Even though this concept began as an experiment, there have been some
use cases that have come up in the wild.

For example, in a [video about Laravel and IronMQ][8] by [UserScape][9], at
about the 7:50 mark they show how you can push a closure onto a queue as a job
so that it can be executed by a worker. This is nice because you do not have to
create a whole class for a job that might be really simple.

## Who Is Using Super Closure?

- [Laravel](https://github.com/laravel/framework) - Serializes a closure to potentially push onto a job queue.
- [HTTP Mock for PHP](https://github.com/InterNations/http-mock) - Serialize a closure to send to remote server within
  a test workflow.
- [Jumper](https://github.com/kakawait/Jumper) - Serialize a closure to run on remote host via SSH.
- [nicmart/Benchmark](https://github.com/nicmart/Benchmark) - Uses the `ClosureParser` to display a benchmarked
  Closure's code.
- Please let me know if and how your project uses Super Closure.

[1]:  https://secure.travis-ci.org/jeremeamia/super_closure.png?branch=master
[2]:  http://travis-ci.org/#!/jeremeamia/super_closure
[3]:  http://packagist.org/packages/jeremeamia/SuperClosure
[4]:  http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
[5]:  http://www.htmlist.com
[6]:  https://github.com/nikic/PHP-Parser
[7]:  http://getcomposer.org
[8]:  http://vimeo.com/64703617
[9]:  http://www.userscape.com
