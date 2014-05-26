<?php

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('Mikulas\\PhpGit\\', __DIR__ . '/src');

$dir = __DIR__ . '/tests/fixtures/repo';
$repo = new \Mikulas\PhpGit\Repo($dir);

foreach ($repo->getCommits() as $commit)
{
	echo "$commit[0]\n$commit[3]\n";

	foreach ($repo->getCommitChanges($commit[0]) as $change)
	{
		echo "    $change[fileB]\n";
		$repo->getFile($change['fileA'], $commit[0]);
		$repo->getFile($change['fileB'], $commit[0]);
	}

	echo "\n";
}

$code = file_get_contents(__DIR__ . '/tests/fixtures/test.php');
$php = new \Mikulas\PhpGit\PhpFile($code);
dump($php);
