<?php
/**
 * PSR-4 compliant autoloader.
 *
 * This will be removed when Composer supports PSR-4 natively.
 *
 * @param string $fqcn The fully-qualified class name.
 *
 * @return void
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 */
spl_autoload_register(function ($fqcn) {
    $prefix = 'Jeremeamia\\SuperClosure\\';
    $baseDir = __DIR__ . '/src/';

    $prefixLength = strlen($prefix);
    if (strncmp($prefix, $fqcn, $prefixLength) !== 0) {
        // Class doesn't match prefix
        return;
    }

    $className = substr($fqcn, $prefixLength);
    $filePath = $baseDir . str_replace('\\', '/', $className) . '.php';
    if (is_readable($filePath)) {
        require $filePath;
    }
});
