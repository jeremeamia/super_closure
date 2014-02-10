<?php

// Set date to UTC to avoid differences with Travis
date_default_timezone_set('UTC');

// Register and fetch the Composer autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';

// Add the Test namespace to the autoloader
$loader->add('SuperClosure\\Test\\Unit', __DIR__ . '/Unit');
$loader->add('SuperClosure\\Test\\Integration', __DIR__ . '/Integration');

// Hack for now because loader does not seem to work
require __DIR__ . '/Unit/UnitTestBase.php';
require __DIR__ . '/Unit/ClosureParser/ConcreteClosureParser.php';
