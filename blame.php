<?php

use Mikulas\PhpGit\AClass;
use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;

list($index, $commits, $names) = require __DIR__ . '/bootstrap.php';

$authors = [];
foreach (array_reverse($commits) as $commit)
{
	list($rev, $time, $email, $subject) = $commit;

//	dump($rev);

	/** @var ChangeSet $set */
	foreach ($index[$rev] as $set)
	{
		foreach ($set->addedClasses as $class)
		{
			$authors[(string) $class][$email] = [
				'originalAuthor' => TRUE,
				'lines' => $class->linesAffected,
			];

			foreach ($class->methods as $method)
			{
				addMethod($authors, $email, $method);
			}
		}
		foreach ($set->renamedClasses as $node)
		{
			list($classA, $classB) = $node;
			$authors[(string) $classB] = $authors[(string) $classA];

			/** @var AMethod $methodA */
			foreach ($classA->methods as $i => $methodA)
			{
				/** @var AMethod $methodB */
				$methodB = $classB->methods[$i];
				$authors[$methodB->toShortString()] = $authors[$methodA->toShortString()];
			}
		}

		foreach ($set->addedMethods as $method)
		{
			addMethod($authors, $email, $method);
		}
		foreach ($set->renamedMethods as $node)
		{
			list($methodA, $methodB) = $node;
			changeMethod($authors, $email, $methodA, $methodB);
		}
		foreach ($set->changedMethods as $method)
		{
			changeMethod($authors, $email, $method);
		}
	}

}
dump($authors);
dump('done');

/**
 * @param array $authors
 * @param string $email
 * @param AMethod $method
 */
function addMethod(array &$authors, $email, $method)
{
	if (!isset($authors[$method->toShortString()]))
	{
		$authors[$method->toShortString()] = [];
	}
	$authors[$method->toShortString()][$email] = [
		'originalAuthor' => TRUE,
		'lines' => $method->linesAffected,
	];
}

/**
 * @param array $authors
 * @param string $email
 * @param AMethod $methodA
 * @param NULL|AMethod $methodB
 */
function changeMethod(array &$authors, $email, $methodA, $methodB = NULL)
{
	$k = $methodB ? $methodB->toShortString() : $methodA->toShortString();

	if ($methodB && !isset($authors[$k]))
	{
		$authors[$k] = $authors[$methodA->toShortString()];
	}

	$lines = $methodB ? max($methodA->linesAffected, $methodB->linesAffected) : $methodA->linesAffected;
	if (!isset($authors[$k]))
	{
		$authors[$k] = [];
	}

	if (!isset($authors[$k][$email]))
	{
		$authors[$k][$email] = [
			'originalAuthor' => FALSE,
			'lines' => $lines,
		];
	}
	else
	{
		$authors[$k][$email]['lines'] += $lines;
	}
}
