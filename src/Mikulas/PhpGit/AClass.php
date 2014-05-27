<?php

namespace Mikulas\PhpGit;


class AClass extends CodeBlock
{

	/** @var AMethod[] */
	public $methods = [];

	/** @var string */
	public $namespace;

	public function __construct($name, $lineFrom, $lineTo, $namespace)
	{
		parent::__construct($name, $lineFrom, $lineTo);
		$this->namespace = $namespace;
	}

}
