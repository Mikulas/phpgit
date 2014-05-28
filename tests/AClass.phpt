<?php

require __DIR__ . '/bootstrap.php';

use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\Edit;
use Mikulas\PhpGit\PhpFile;
use Tester\Assert;

$code = file_get_contents(__DIR__ . '/fixtures/signatures.php');
$a = new PhpFile($code);

Assert::same([3, 6], $a->classes[0]->getSignatureLines());
Assert::same([7, 30], $a->classes[0]->getBodyLines());
