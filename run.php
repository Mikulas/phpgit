<?php

/** @var \Composer\Autoload\ClassLoader $loader */
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
//		echo "    $change[fileA] : $change[fileB]\n";

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

		foreach ($change['changes'] as $edit)
		{
			$removed = [];
			if ($phpA && $edit['lengthA'] > 0)
			{
				$removed = array_merge($removed, $phpA->getBetweenLines($edit['beginA'], $edit['beginA'] + $edit['lengthA'] - 1));
			}
			$added = [];
			if ($phpB && $edit['lengthB'] > 0)
			{
				$added = array_merge($added, $phpB->getBetweenLines($edit['beginB'], $edit['beginB'] + $edit['lengthB'] - 1));
			}

			if ($removed && $added)
			{
				if (count($removed) === 1 && count($added) === 1)
				{
					if (count($removed[0]->methods) === 1 && count($added[0]->methods) === 1)
					{
						$old = $removed[0]->methods[0];
						$new = $added[0]->methods[0];
						if ($old->name !== $new->name)
						{
							$class = $removed[0];
							echo "- renamed {$class}::{$old}\n";
							echo "-      to {$class}::{$new}\n";
						}
					}
				}
			}
			else
			{
				foreach ($removed as $rem)
				{
					echo "- removed {$rem}\n";
				}
				foreach ($added as $add)
				{
					echo "-   added {$add}\n";
				}
			}
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
