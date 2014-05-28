<?php

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Mikulas\\PhpGit\\', __DIR__ . '/../src');
require __DIR__ . '/../vendor/nette/tester/Tester/bootstrap.php';
\Tracy\Debugger::$maxDepth = 5;
