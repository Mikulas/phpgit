<?php

namespace Mikulas\PhpGit;


class AMethod extends CodeBlock
{
	/** @var AClass */
	public $class;

	public function __construct($name, $lineFrom, $lineTo, AClass $class)
	{
		parent::__construct($name, $lineFrom, $lineTo);
		$this->class = $class;
	}


	public function __toString()
	{
		return "{$this->class}::{$this->name}";
	}

}
