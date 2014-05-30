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

		$noDefaults = $php->classes[0]->methods[1];
		Assert::same('$a, $b, $c', $noDefaults->getParamSignature());

		$empty = $php->classes[0]->methods[2];
		Assert::same('', $empty->getParamSignature());
	}

	public function testGetParamSignatureNull()
	{
		$code = file_get_contents(__DIR__ . '/fixtures/AMethod/getParamSignatureNull.php');
		$php = new PhpFile($code);
		$method = $php->classes[0]->methods[0];
		Assert::same('$a = NULL', $method->getParamSignature());

		$method = $php->classes[0]->methods[1];
		Assert::same('$a = NULL', $method->getParamSignature());

		$method = $php->classes[0]->methods[2];
		Assert::same('string $a = NULL', $method->getParamSignature());

		$method = $php->classes[0]->methods[3];
		Assert::same('NULL|string $a = \'test\'', $method->getParamSignature());
	}

}

(new AMethodTest())->run();
