<?php

/** @var \Composer\Autoload\ClassLoader $loader */
use Mikulas\PhpGit\AClass;
use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\PhpFile;
use Mikulas\PhpGit\Repo;


$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('Mikulas\\PhpGit\\', __DIR__ . '/src');

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);
\Tracy\Debugger::$strictMode = TRUE;
\Tracy\Debugger::$maxDepth = 4;

$dir = __DIR__ . '/tests/fixtures/repo';
$repo = new Repo($dir);

$start = microtime(TRUE);

$parent = NULL;
$cache = [];
$commits = $repo->getCommits();

$cacheFile = $dir . '/.git/php_index.bin';
$index = file_exists($cacheFile) ? unserialize(file_get_contents($cacheFile)) : [];

$printBuildingIndex = 10;
foreach (array_reverse($commits) as $commit)
{
	list($rev, $time, $author, $subject) = $commit;
	if (isset($index[$rev]))
	{
		continue;
	}
	$index[$rev] = [];

	$printBuildingIndex--;
	if ($printBuildingIndex === 0)
	{
		echo "Building index, please wait...\n";
	}
	else if ($printBuildingIndex < 0)
	{
		echo ".";
	}

	foreach ($repo->getCommitChanges($commit[0]) as $change)
	{
		if (stripos(strrev(strToLower($change['fileA'])), 'php') !== 0
		 && stripos(strrev(strToLower($change['fileB'])), 'php') !== 0)
		{
			continue;
		}

		$phpA = isset($cache[$parent][$change['fileA']])
			? $cache[$parent][$change['fileA']]
			: getPhp($repo, $parent, $change['fileA']);
		$cache[$parent][$change['fileA']] = $phpA;

		$phpB = isset($cache[$rev][$change['fileB']])
			? $cache[$rev][$change['fileB']]
			: getPhp($repo, $rev, $change['fileB']);
		$cache[$parent][$change['fileA']] = $phpB;

		$set = new ChangeSet($phpA, $phpB, $change['edits']);
		$index[$rev][] = $set;
	}

	unset($cache[$parent]);
	$parent = $commit[0];
}
file_put_contents($dir . '/.git/php_index.bin', serialize($index));

foreach ($commits as $commit)
{
	list($rev, $time, $author, $subject) = $commit;

	/** @var ChangeSet $set */
	$skip = TRUE;
	foreach ($index[$rev] as $set)
	{
		if ($set->containsChange()) {
			$skip = FALSE;
			break;
		}
	}
	if ($skip)
	{
		continue;
	}

	echo "\033[33mcommit $rev\033[0m\n";
	echo "Author: $author\n";
	echo "Date: " . date('r', $time) . "\n";
	echo "    $subject\n";

	$printNewline = TRUE;

	/** @var ChangeSet $set */
	foreach ($index[$rev] as $set)
	{
		if ($printNewline && $set->containsChange())
		{
			echo "\n";
			$printNewline = FALSE;
		}

		foreach ($set->removedClasses as $class)
		{
			echo "    removed $class\n";
		}
		foreach ($set->removedMethods as $method)
		{
			echo "    removed $method\n";
		}
		foreach ($set->renamedClasses as $node)
		{
			/** @var AClass $a */
			/** @var AClass $b */
			list($a, $b) = $node;
			echo "    renamed $a\n";
			echo "         to $b\n";
		}
		foreach ($set->renamedMethods as $node)
		{
			/** @var AMethod $a */
			/** @var AMethod $b */
			list($a, $b) = $node;
			echo "    renamed $a\n";
			echo "         to $b\n";
		}
		foreach ($set->addedClasses as $class)
		{
			echo "      added $class\n";
		}
		foreach ($set->addedMethods as $method)
		{
			echo "      added $method\n";
		}
		foreach ($set->changedMethodParameters as $node)
		{
			list($methodA, $methodB) = $node;
			echo "    changed $methodA\n";
			echo "         to $methodB\n";
		}
		foreach ($set->changedMethods as $method)
		{
			echo "    changed $method\n";
		}
	}

	echo "\n";
}

function getPhp(Repo $repo, $revision, $path)
{
	if ($path === NULL) {
		return NULL;
	}
	$code = $repo->getFile($revision, $path);
	if (strLen($code) > 1e5) {
		// skip large presumably minified files
		return NULL;
	}
	return new PhpFile($code);
}

dump(round((microtime(TRUE) - $start) * 1000, 1) . ' ms');
dump(round(memory_get_peak_usage(TRUE) / 1e6, 2) . ' MB');
