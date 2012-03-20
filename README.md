# SuperClosure

The PHP SuperClosure by Jeremy Lindblom.

NOTES: The original SuperClosure class was written as a experiment for an article on [HTMList.com][htmlist] called
[Extending PHP 5.3 Closures with Serialization and Reflection][article]. The original code for that class is located in
this repository at [/src/_legacy/SuperClosure.php][legacy], and should not be used in production. Please use the
updated, properly-namespaced, and better-tested version of SuperClosure in this repository by install SuperClosure via
Composer. Please see the installation section below.

## Purpose

The SuperClosure is a wrapper for a regular closure that allows serialization, code retrieval, and easy reflection. 
PHP closures cannot be serialized by normal means, so the SuperClosure enables serialization by using reflection and
tokenizing/parsing to get all the information about the closure it needs to recreate it. This is includes the actual
code defining the function and the names and values of any variables in the `use` statement of the closure.

## General Use

Check it out!

    use SuperClosure/SuperClosure;

	$foo = 2;
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
	echo  PHP_EOL . "CODE:" . PHP_EOL . $code;

## Installation

The SuperClosure relies on the [FunctionParser library][parser], which requires the Reflection API and also on the PHP 
tokenizer (`token_get_all()`). PHP must be compiled with the `--enable-tokenizer` flag in order for the tokenizer to be
available. You must also be using PHP 5.3, since this deals with closures.

Requirements:

- **PHP 5.3.2+**
- **PHPUnit** for tests
- **Composer** for consuming FunctionParser as a dependency

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
developers, but their use is required to created the serialization/unserialization functionality of SuperClosure.



[htmlist]: http://htmlist.com
[article]: http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
[legacy]:  https://github.com/jeremeamia/super_closure/blob/master/src/_legacy/SuperClosure.php
[parser]:  https://github.com/jeremeamia/FunctionParser
