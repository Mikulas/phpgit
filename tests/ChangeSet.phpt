<?php

namespace Tests;

use Mikulas\PhpGit\AMethod;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


class ChangeSetTest extends TestCase
{

	public function testClassIsRenamed()
	{
		$set = $this->getSet();
		Assert::same(1, count($set->renamedClasses));
		Assert::same('OriginalName', $set->renamedClasses[0][0]->name);
		Assert::same('NewName', $set->renamedClasses[0][1]->name);
	}

	public function testClassIsAdded()
	{
		$set = $this->getSet();
		Assert::same(1, count($set->addedClasses));
		Assert::same('AddedClass', $set->addedClasses[0]->name);
	}

	public function testClassIsRemoved()
	{
		$set = $this->getSet();
		Assert::same(1, count($set->removedClasses));
		Assert::same('RemovedClass', $set->removedClasses[0]->name);
	}

	public function testMethodIsAdded()
	{
		$set = $this->getSet();
		Assert::same(1, count($set->addedMethods));
		Assert::same('addedMethod', $set->addedMethods[0]->name);
	}

	public function testMethodIsRenamed()
	{
		$set = $this->getSet();
		Assert::same(1, count($set->renamedMethods));
		Assert::same('firstName', $set->renamedMethods[0][0]->name);
		Assert::same('secondName', $set->renamedMethods[0][1]->name);
	}

	public function testMethodSignatureIsChanged()
	{
		$set = $this->getSet();
		Assert::same(1, count($set->changedMethodParameters));
		/** @var AMethod $methodA */
		/** @var AMethod $methodB */
		list($methodA, $methodB) = $set->changedMethodParameters[0];
		Assert::same('array $a, $b, $c = NULL', $methodA->getParamSignature());
		Assert::same('Foo[] $a, $x, int $c = NULL', $methodB->getParamSignature());
	}

	public function testMethodSignatureIsNotChanged()
	{
		$set = $this->getSet();
		Assert::same(0, count($set->changedMethodParameters));
	}
}

(new ChangeSetTest())->run();
