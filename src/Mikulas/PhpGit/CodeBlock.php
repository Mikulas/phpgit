<?php

namespace Mikulas\PhpGit;


abstract class CodeBlock
{

	/** @var string */
	public $name;

	/** @var int */
	public $lineFrom;

	/** @var int */
	public $lineTo;

	/**
	 * @param string $name
	 * @param int $lineFrom
	 * @param int $lineTo
	 */
	public function __construct($name, $lineFrom, $lineTo)
	{
		$this->name = $name;
		$this->lineFrom = $lineFrom;
		$this->lineTo = $lineTo;
	}

}
