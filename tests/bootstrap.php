<?php

date_default_timezone_set('UTC');
require __DIR__ . '/../vendor/autoload.php';

// Register another PSR-4-compliant autoloader for loading test classes
spl_autoload_register(function ($fqcn) {
    $prefix = 'Jeremeamia\\SuperClosure\\Test\\';
    $baseDir = __DIR__ . '/';

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
