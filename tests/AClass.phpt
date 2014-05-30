<?php

namespace Tests;

use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\PhpFile;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class AClassTest extends TestCase
{

	public function testGetLines()
	{
		$code = file_get_contents(__DIR__ . '/fixtures/AClass/getLines.php');
		$php = new PhpFile($code);

		$class = $php->classes[0];
		Assert::same([3, 6], $class->getSignatureLines());
		Assert::same([7, 9], $class->getBodyLines());
	}

}

(new AClassTest())->run();
