<?php

require __DIR__ . '/bootstrap.php';

use Tester\Assert;

$dir = __DIR__ . '/fixtures/repo';
$repo = new \Mikulas\PhpGit\Repo($dir);
$commits = $repo->getCommits();

Assert::same(268, count($commits));
Assert::same([
	'f5f5a9fe327029b617e6595c6c94d9d80f4503f8',
	'1400673617',
	'tsusanka@gmail.com',
	'All: vendor/autoload.php is not ignored'
], $commits[0]);

$changes = $repo->getCommitChanges('511de328');
Assert::same(3, count($changes));
Assert::same([
	'fileA' => 'app/config/Configurator.php',
	'fileB' => 'app/config/Configurator.php',
	'changes' => [
		['beginA' => 17, 'lengthA' => 1, 'beginB' => 17, 'lengthB' => 1],
		['beginA' => 62, 'lengthA' => 1, 'beginB' => 62, 'lengthB' => 1],
		['beginA' => 100, 'lengthA' => 0, 'beginB' => 101, 'lengthB' => 10],
	],
], $changes[0]);

$newFile = $repo->getCommitChanges('ff82630');
Assert::null($newFile[0]['fileA']);

$removedFile = $repo->getCommitChanges('57dcef6');
Assert::null($removedFile[1]['fileB']);

Assert::same('05bde416d8744726e4c0aeb55beb33f1', md5($repo->getFile('8608fc1', 'composer.json')));
