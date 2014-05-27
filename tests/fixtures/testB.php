<?php

namespace Name\Space;

class ClassName
{

	/**
	 * PHPDoc change
	 *
	 * @param ClassName $arg1
	 * @param array $arg2
	 * @param null $arg3
	 *
	 * @return string
	 */
	public function methodName($arg1, array $arg2, $arg3 = NULL)
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
