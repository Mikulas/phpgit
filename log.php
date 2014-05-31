<?php

use Mikulas\PhpGit\AClass;
use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\ComposerUpdate;


list($index, $commits, $names) = require __DIR__ . '/bootstrap.php';

foreach ($commits as $commit)
{
	list($rev, $time, $email, $subject) = $commit;
	if ((!$index[$rev]->changeset || !$index[$rev]->changeset->containsChange()) && !$index[$rev]->composer)
	{
		continue;
	}

	$name = $names[$email];
	echo "\033[33mcommit $rev\033[0m\n";
	echo "Author: $name <$email>\n";
	echo "Date: " . date('r', $time) . "\n";
	echo "    $subject\n";

	$printNewline = TRUE;

	$composer = $index[$rev]->composer;
	if ($composer)
	{
		/** @var ComposerUpdate $set */
		echo "\n";

		echo "    updated dependencies:\n";
		foreach ($composer->removed as $name)
		{
			echo "        removed $name\n";
		}
		foreach ($composer->added as $name)
		{
			echo "          added $name\n";
		}
		foreach ($composer->updated as $name => $versions)
		{
			list($a, $b) = $versions;
			echo "        updated $name\n                $a => $b\n";
		}
	}
	echo "\n";

	/** @var ChangeSet $set */
	$set = $index[$rev]->changeset;

	foreach ($set->removedClasses as $class)
	{
		echo "    removed $class\n";
	}
	foreach ($set->removedMethods as $method)
	{
		echo "    removed $method\n";
	}
	foreach ($set->addedClasses as $class)
	{
		echo "      added $class\n";
	}
	foreach ($set->addedMethods as $method)
	{
		echo "      added $method\n";
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

	echo "\n";
}
