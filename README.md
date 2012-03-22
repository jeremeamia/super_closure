# SuperClosure

The **PHP SuperClosure** by Jeremy Lindblom.

[![Build Status][ci-status]][travis-ci]

> The original SuperClosure class was written as an experiment for an article on [HTMList.com][htmlist] called
[Extending PHP 5.3 Closures with Serialization and Reflection][article]. The original code for that class is located in
this repository at [/src/_legacy/SuperClosure.php][legacy], and should *not* be used in production. Please use the
updated, properly-namespaced, and better-tested version of SuperClosure in this repository by installing SuperClosure via
Composer. Please see the installation section below for instructions.

## Purpose

The SuperClosure is a wrapper for a regular closure that allows serialization, code retrieval, and easy reflection.
PHP closures cannot be serialized by normal means, so the SuperClosure enables serialization by using reflection and
tokenizing/parsing to get all the information about the closure it needs to recreate it. This is includes the actual
code defining the function and the names and values of any variables in the `use` statement of the closure.

## General Use

Check it out!

	use SuperClosure/SuperClosure;

	$foo     = 2;
	$closure = function($bar) use($foo) {
	    return $foo + $bar;
	};

	$closure = new SuperClosure($closure);

	$original_result = $closure(8);
	$serialized      = serialize($super_closure);
	$unserialized    = unserialize($serialized);
	$final_result    = $unserialized(8);

	if ($original_result === $final_result) {
		echo "It's working!" . PHP_EOL;
	}

	$code = $closure->getCode();
	echo "CODE: {$code}" . PHP_EOL;

## Installation

The SuperClosure relies on the [FunctionParser library][parser], which requires the Reflection API and also the PHP
tokenizer (`token_get_all()`). PHP must be compiled with the `--enable-tokenizer` flag in order for the tokenizer to be
available. You must be using PHP 5.3, since this deals with closures.

### Requirements:

- **PHP 5.3.2+**
- **PHPUnit** for tests
- **[Composer][composer]** for consuming FunctionParser as a dependency

To install SuperClosure as a dependency of your project using Composer, please add the following to your
`composer.json` config file.

    {
        "require": {
            "jeremeamia/SuperClosure": "*"
        }
    }

Then run `php composer.phar install --install-suggests` from your project's root directory to install the SuperClosure.

## Warning

The SuperClosure class uses the `extract()` and `eval()` functions. These functions considered dangerous by many
developers, but their use is required to create the serialization/unserialization functionality of SuperClosure.

## Links

- [SuperClosure on Packagist][packagist]
- [SuperClosure on Travis CI][travis-ci]



[htmlist]:   http://htmlist.com
[article]:   http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
[legacy]:    https://github.com/jeremeamia/super_closure/blob/master/src/_legacy/SuperClosure.php
[parser]:    https://github.com/jeremeamia/FunctionParser
[packagist]: http://packagist.org/packages/jeremeamia/SuperClosure
[composer]:  http://getcomposer.org
[travis-ci]: http://travis-ci.org/#!/jeremeamia/super_closure
[ci-status]: https://secure.travis-ci.org/jeremeamia/super_closure.png?branch=master
