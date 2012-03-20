# SuperClosure

The PHP Super Closure by Jeremy Lindblom.

## Purpose

???

## General Use

???

## Warning

???

## Installation

The SuperClosure relies on the FunctionParser library, which requires the Reflection API and also on the PHP tokenizer
(`token_get_all()`). PHP must be compiled with the `--enable-tokenizer` flag in order for the tokenizer to be
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
