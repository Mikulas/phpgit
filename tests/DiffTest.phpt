<?php

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class DiffTest extends Tester\TestCase
{

	public function testCompare()
	{
		$repo = new Repository('/Users/mikulas/Projects/respekt');
		$comp = new Comparator();
		$parser = new \PhpParser\Parser(new \PhpParser\Lexer());

		$diff = new Diff($repo, $comp, $parser);
		$diff->compare('HEAD~10', 'HEAD');

		Assert::false(TRUE);
	}

}

(new DiffTest)->run();
