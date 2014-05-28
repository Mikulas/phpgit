<?php

require __DIR__ . '/bootstrap.php';

use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\Edit;
use Mikulas\PhpGit\PhpFile;
use Tester\Assert;

$code = file_get_contents(__DIR__ . '/fixtures/signatures.php');
$a = new PhpFile($code);

Assert::same([6, 14], $a->classes[0]->methods[0]->getSignatureLines());
Assert::same([19, 23], $a->classes[0]->methods[1]->getSignatureLines());

Assert::same([15, 17], $a->classes[0]->methods[0]->getBodyLines());
Assert::same([24, 25], $a->classes[0]->methods[1]->getBodyLines());
