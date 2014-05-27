<?php

namespace Name\Space;

class ClassName
{

	/**
	 * @param ClassName $arg1
	 * @param array $arg2
	 * @param null $arg3
	 *
	 * @return string
	 */
	public function changedMethod($arg1, array $arg2, $arg3 = NULL)
	{
		$test = 'trap function methodName() {}';
		return $test;
	}

	public function anotherMethod()
	{

	}

}

class AnotherClass
{

	public function methodToRemove()
	{

	}

}

// something something

// delimiter 2

class ToRemove {}
