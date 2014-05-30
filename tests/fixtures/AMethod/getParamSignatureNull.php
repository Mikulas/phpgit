<?php

class Foo
{

	public function null1($a = NULL) {}

	/**
	 * @param NULL $a
	 */
	public function null2($a = NULL) {}

	/**
	 * @param NULL|string $a
	 */
	public function null3($a = NULL) {}

	/**
	 * @param NULL|string $a
	 */
	public function null4($a = 'test') {}

}
