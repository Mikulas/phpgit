<?php

use Mikulas\PhpGit\AClass;
use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;


list($index, $commits, $authors) = require __DIR__ . '/bootstrap.php';

foreach ($commits as $commit)
{
	list($rev, $time, $email, $subject) = $commit;

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

	$name = $authors[$email];
	echo "\033[33mcommit $rev\033[0m\n";
	echo "Author: $name <$email>\n";
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
