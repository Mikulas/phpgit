<?php

use PhpParser\Node\Stmt\Namespace_;


/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('Mikulas\\PhpGit\\', __DIR__ . '/src');

$code = file_get_contents(__DIR__ . '/tests/fixtures/test.php');
$php = new \Mikulas\PhpGit\PhpFile($code);
dump($php);
