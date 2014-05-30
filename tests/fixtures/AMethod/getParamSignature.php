<?php

class Foo
{

	const BAR = 'bar';

	/**
	 * @param Foo[] $a
	 * @param float $b
	 * @param string|NULL $c
	 */
	public function test(array $a = array(), $b = 1.0, $c = self::BAR)
	{

	}

}
