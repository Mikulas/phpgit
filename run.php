<?php

use PhpParser\Node\Stmt\Namespace_;


/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('Mikulas\\PhpGit\\', __DIR__ . '/src');


$dir = __DIR__ . '/tests/fixtures/repo';
$repo = new \Mikulas\PhpGit\Repo($dir);
dump($repo->getCommits());

$code = file_get_contents(__DIR__ . '/tests/fixtures/test.php');
$php = new \Mikulas\PhpGit\PhpFile($code);
dump($php);
