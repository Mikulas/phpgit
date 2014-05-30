<?php

namespace Mikulas\PhpGit;


use PhpParser\Comment\Doc;


abstract class CodeBlock
{

	/** @var string */
	public $name;

	/** @var int */
	public $lineFrom;

	/** @var int */
	public $lineTo;

	/** @var NULL|boolean was this code block completely removed or added? */
	public $complete;

	/** @var NULL|boolean */
	public $changedSignature;

	/** @var NULL|boolean */
	public $changedBody;

	/** @var Doc */
	public $phpdoc;

	/**
	 * @param string $name
	 * @param int $lineFrom
	 * @param int $lineTo
	 * @param Doc $phpdoc
	 */
	public function __construct($name, $lineFrom, $lineTo, $phpdoc)
	{
		$this->name = $name;
		$this->lineFrom = $phpdoc ? $phpdoc->getLine() : $lineFrom - 1; // -1 to catch new annotations
		$this->signatureFrom = $lineFrom;
		$this->lineTo = $lineTo;
		$this->phpdoc = $phpdoc;
	}

}
