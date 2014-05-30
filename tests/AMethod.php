<?php

namespace Tests;

use Mikulas\PhpGit\AMethod;
use Mikulas\PhpGit\PhpFile;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class AMethodTest extends TestCase
{

	public function testGetParamSignature()
	{
		$code = file_get_contents(__DIR__ . '/fixtures/AMethod/getParamSignature.php');
		$php = new PhpFile($code);
		$method = $php->classes[0]->methods[0];
		Assert::same('Foo[] $a = array(), float $b = 1.0, string|NULL $c = self::BAR', $method->getParamSignature());
	}
}

(new AMethodTest())->run();
