<?php

require __DIR__ . '/bootstrap.php';

use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\Edit;
use Mikulas\PhpGit\PhpFile;
use Tester\Assert;

$code = file_get_contents(__DIR__ . '/fixtures/signatures.php');
$a = new PhpFile($code);

Assert::same([9, 17], $a->classes[0]->methods[0]->getSignatureLines());
Assert::same([18, 20], $a->classes[0]->methods[0]->getBodyLines());

Assert::same([22, 26], $a->classes[0]->methods[1]->getSignatureLines());
Assert::same([27, 28], $a->classes[0]->methods[1]->getBodyLines());
