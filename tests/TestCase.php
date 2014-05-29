<?php

namespace Tests;


use Mikulas\PhpGit\ChangeSet;
use Mikulas\PhpGit\Edit;
use Mikulas\PhpGit\PhpFile;


class TestCase extends \Tester\TestCase
{

	/**
	 * @return ChangeSet
	 */
	protected function getSet()
	{
		$stack = debug_backtrace();
		$case = substr($stack[1]['class'], 6, -4);
		$test = lcFirst(substr($stack[1]['function'], 4));

		$fileA = __DIR__ . "/fixtures/{$case}/{$test}A.php";
		$code = file_get_contents($fileA);
		$a = new PhpFile($code);

		$fileB = __DIR__ . "/fixtures/{$case}/{$test}B.php";
		$code = file_get_contents($fileB);
		$b = new PhpFile($code);

		$edits = $this->getEdits($fileA, $fileB);

		return new ChangeSet($a, $b, $edits);
	}

	protected function getEdits($fileA, $fileB)
	{
		$cmd = sprintf('git diff -U0 --no-index %s %s',
			escapeshellarg($fileA),
			escapeshellarg($fileB)
		);
		$p = new \Symfony\Component\Process\Process($cmd);
		$p->run();

		$edits = [];
		foreach (explode("\n", $p->getOutput()) as $line)
		{
			$m = [];
			if (preg_match('~@@ -(\d+)(?:,(\d+))? \+(\d+)(?:,(\d+))? @@~', $line, $m))
			{
				$edits[] = new Edit($m[1], $m[2] ?: 1, $m[3], isset($m[4]) && $m[4] ? $m[4] : 1);
			}
		}

		return $edits;
	}

}
