<?php

require __DIR__ . '/bootstrap.php';

use Mikulas\PhpGit\PhpFile;
use Tester\Assert;

$code = file_get_contents(__DIR__ . '/fixtures/test.php');
$php = new PhpFile($code);

Assert::same([], $php->getBetweenLines(0, 4));

$found = $php->getBetweenLines(5, 5);
Assert::same(1, count($found));
Assert::same(0, count($found[0]->methods));

$found = $php->getBetweenLines(24, 33);
Assert::same(2, count($found));
Assert::same(1, count($found[0]->methods));
Assert::same(1, count($found[1]->methods));
