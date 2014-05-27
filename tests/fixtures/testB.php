<?php

namespace Name\Space;

class ClassName
{

	public function addedMethod() {}

	/**
	 * PHPDoc change
	 *
	 * @param ClassName $arg1
	 * @param Foo[] $arg2
	 * @param NULL|string $arg3
	 *
	 * @return string
	 */
	public function changedMethod($arg1, array $arg2, $arg3 = NULL)
	{
		$test = 'trap function methodName() {}';
		$change = TRUE;
		return $test;
	}

	public function renamedMethod()
	{

	}

}

class RenamedClass
{

}

// something something

class NewClass
{

}

// delimiter 2
