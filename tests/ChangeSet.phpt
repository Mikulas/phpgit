<?php

require __DIR__ . '/bootstrap.php';

use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\Edit;
use Mikulas\PhpGit\PhpFile;
use Tester\Assert;

$code = file_get_contents(__DIR__ . '/fixtures/test.php');
$a = new PhpFile($code);
$code = file_get_contents(__DIR__ . '/fixtures/testB.php');
$b = new PhpFile($code);

$edits = [
	new Edit(7, 0, 8, 1),
	new Edit(9, 0, 11, 2),
	new Edit(18, 0, 22, 1),
	new Edit(21, 1, 26, 1),
	new Edit(28, 1, 33, 1),
	new Edit(31, 4, 35, 0),
	new Edit(39, 0, 40, 4),
	new Edit(42, 1, 46, 0),
];

$set = new ChangeSet($a, $b, $edits);

Assert::same(1, count($set->renamedClasses));
Assert::same('AnotherClass', $set->renamedClasses[0][0]->name);
Assert::same('RenamedClass', $set->renamedClasses[0][1]->name);

Assert::same(1, count($set->addedClasses));
Assert::same('NewClass', $set->addedClasses[0]->name);

Assert::same(1, count($set->removedClasses));
Assert::same('ToRemove', $set->removedClasses[0]->name);

Assert::same(1, count($set->renamedMethods));
Assert::same('anotherMethod', $set->renamedMethods[0][0]->name);
Assert::same('renamedMethod', $set->renamedMethods[0][1]->name);

Assert::same(1, count($set->removedMethods));
Assert::same('methodToRemove', $set->removedMethods[0]->name);

Assert::same(1, count($set->addedMethods));
Assert::same('addedMethod', $set->addedMethods[0]->name);