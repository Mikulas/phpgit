<?php

namespace Mikulas\PhpGit;


class AClass extends CodeBlock
{

	/** @var AMethod[] */
	public $methods = [];

	/** @var string */
	public $namespace;

	public function __construct($name, $lineFrom, $lineTo, $phpdoc, $namespace)
	{
		parent::__construct($name, $lineFrom, $lineTo, $phpdoc);
		$this->namespace = $namespace;
	}

	public function __toString()
	{
		return "{$this->namespace}\\{$this->name}";
	}

	/**
	 * @return int[] begin end
	 */
	public function getSignatureLines()
	{
		return [$this->phpdoc ? $this->phpdoc->getLine() : $this->lineFrom, $this->lineFrom];
	}

	/**
	 * @return int[] begin end
	 */
	public function getBodyLines()
	{
		return [$this->lineFrom + 1, $this->lineTo];
	}

}
