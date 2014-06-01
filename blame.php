<?php

use Mikulas\PhpGit\AClass;
use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\PhpFile;


list($index, $commits, $names) = require __DIR__ . '/bootstrap.php';

$authors = [];
foreach (array_reverse($commits) as $commit)
{
	list($rev, $time, $email, $subject) = $commit;

	/** @var ChangeSet $set */
	$set = $index[$rev]->changeset;
	if (!$set)
	{
		continue;
	}
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

		$authors[(string) $classB] = isset($authors[(string) $classA])
			? $authors[(string) $classA]
			: []; // might not be set due to parse errors

		/** @var AMethod $methodA */
		foreach ($classA->methods as $i => $methodA)
		{
			/** @var AMethod $methodB */
			$methodB = $classB->methods[$i];
			$authors[$methodB->toShortString()] = isset($authors[$methodA->toShortString()])
				? $authors[$methodA->toShortString()]
				: []; // might not be set due to parse errors
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
//dump($authors);
dump('done');

$file = file_get_contents($argv[2]);
$match = [];
preg_match('~namespace\s*(?P<ns>([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\\\?)+)\s*;~i', $file, $match);
$ns = isset($match['ns']) ? $match['ns'] : NULL;
$regexes = [
	'class' => '~^(?P<space>[ \t]*)(?P<phpdoc>/\*\*.*?\*/)?(?P<rest>\s*(final|abstract)?\s*class\s+(?P<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))~ims',
	'method' => '~^(?P<space>[ \t]*)(?P<phpdoc>/\*\*((?!\\*/).)*?\*/)?(?P<rest>\s*((public|protected|private|abstract|final|static)\s+)*\s*function\s+(?P<name>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*))~ims',
];
$class = NULL;
foreach ($regexes as $type => $regex)
{
	$file = preg_replace_callback($regex, function($m) use ($ns, $authors, $names, $type, &$class) {
		if ($type === 'class')
		{
			$signature = $ns . '\\' . $m['name'];
			$class = $signature;
		}
		else
		{
			dump($m);
			$signature = "$class::$m[name]";
		}
		$lines = [];
		$totalLines = 0;
		foreach ($authors[$signature] as $info)
		{
			$totalLines += $info['lines'];
		}
		foreach ($authors[$signature] as $email => $info)
		{
			$name = $names[$email];
			if (count($authors[$signature]) > 1)
			{
				$post = $info['originalAuthor'] ? ', original author' : '';
				$count = round($info['lines'] / $totalLines * 100, 0);
				$lines[] = "$m[space] * @author $name <$email> $count %$post";
			}
			else
			{
				$lines[] = "$m[space] * @author $name <$email>";
			}
		}
		if ($m['phpdoc'])
		{
			$lines[] = "$m[space] *";
			$docEnd = substr($m['phpdoc'], 4);
			return "$m[space]/**\n\x02" . implode("\n", $lines) . "\n\x03$docEnd" . $m['rest'];
		}
		else
		{
			return "$m[space]\x02/*" . implode("\n", $lines) . "\n */\x03" . $m['rest'];
		}

	}, $file);
}
echo $file;

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
