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
		return [$this->lineFrom, $this->signatureFrom];
	}

	/**
	 * @return int[] begin end
	 */
	public function getBodyLines()
	{
		return [$this->signatureFrom + 1, $this->lineTo];
	}

}
