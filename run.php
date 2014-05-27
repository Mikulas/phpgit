<?php

/** @var \Composer\Autoload\ClassLoader $loader */
use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\PhpFile;
use Mikulas\PhpGit\Repo;


$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('Mikulas\\PhpGit\\', __DIR__ . '/src');

\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);
\Tracy\Debugger::$strictMode = TRUE;

$dir = __DIR__ . '/tests/fixtures/repo';
$repo = new Repo($dir);

$start = microtime(TRUE);

$parent = NULL;
$cache = [];
$commits = array_reverse($repo->getCommits());

$cacheFile = $dir . '/.git/php_index.bin';
$index = file_exists($cacheFile) ? unserialize(file_get_contents($cacheFile)) : [];
$index = [];
foreach ($commits as $commit)
{
	$rev = $commit[0];
	if (isset($index[$rev]))
	{
		continue;
	}
	$index[$rev] = [];

	echo "$rev\n$commit[3]\n";

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

		$set = new \Mikulas\PhpGit\ChangeSet($phpA, $phpB, $change['edits']);
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
	}

	unset($cache[$parent]);
	$parent = $commit[0];
	echo "\n";
}
file_put_contents($dir . '/.git/php_index.bin', serialize($index));

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

$code = file_get_contents(__DIR__ . '/tests/fixtures/test.php');
$php = new PhpFile($code);
dump($php);
dump(round(microtime(TRUE) - $start, 1) . ' seconds');
dump(memory_get_peak_usage(TRUE));
