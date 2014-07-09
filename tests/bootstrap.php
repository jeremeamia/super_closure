<?php

// Set date to UTC to avoid differences with Travis
date_default_timezone_set('UTC');

// Register and fetch the Composer autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';

// Add the Test namespace to the autoloader
$loader->addPsr4('SuperClosure\\Test\\Unit\\', __DIR__ . '/Unit');
$loader->addPsr4('SuperClosure\\Test\\Integration\\', __DIR__ . '/Integration');
