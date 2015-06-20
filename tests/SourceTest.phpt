<?php

use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class SourceTest extends Tester\TestCase
{

	public function testGetSimplified()
	{
		$rawA = file_get_contents(__DIR__ . '/fixtures/a.php');

		$parser = new PhpParser\Parser(new PhpParser\Lexer);

		$source = new Source($parser, $rawA);

		Assert::count(1, $source->getSimplified());
		dump($source);
		Assert::false(true);
	}

}

(new SourceTest)->run();
