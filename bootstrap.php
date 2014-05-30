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
$authors = $repo->getAuthors();

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

		$f = $change['fileA'];
		$phpA = isset($cache[$parent][$f])
			? $cache[$parent][$f]
			: getPhp($repo, $parent, $f);
		$cache[$parent][$change['fileA']] = $phpA;

		$f = $change['fileB'];
		$phpB = isset($cache[$rev][$f])
			? $cache[$rev][$f]
			: getPhp($repo, $rev, $f);
		$cache[$parent][$change['fileB']] = $phpB;

		$set = new ChangeSet($phpA, $phpB, $change['edits']);
		$index[$rev][] = $set;
	}

	unset($cache[$parent]);
	$parent = $commit[0];
}
file_put_contents($dir . '/.git/php_index.bin', serialize($index));

return [$index, $commits, $authors];


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
