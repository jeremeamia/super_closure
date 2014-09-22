<?php

// Set date to UTC to avoid differences with Travis
date_default_timezone_set('UTC');

// Register and fetch the Composer autoloader
$loader = require __DIR__ . '/../vendor/autoload.php';
