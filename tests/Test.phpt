<?php

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class FooTest extends Tester\TestCase
{

	public function testSomething()
	{
		$rawA = file_get_contents(__DIR__ . '/fixtures/a.php');
		$rawB = file_get_contents(__DIR__ . '/fixtures/b.php');

		$parser = new PhpParser\Parser(new PhpParser\Lexer);
		try {
			$stmtsA = $parser->parse($rawA);
			$stmtsB = $parser->parse($rawB);
			var_dump($stmtsA);
			var_dump($stmtsB);

		} catch (PhpParser\Error $e) {
			echo 'Parse Error: ', $e->getMessage();
		}

		Assert::true(TRUE);
	}

}

(new FooTest)->run();
