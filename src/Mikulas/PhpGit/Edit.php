<?php

namespace Mikulas\PhpGit;


class Edit
{

	/** @var int */
	public $beginA;

	/** @var int */
	public $lengthA;

	/** @var int */
	public $beginB;

	/** @var int */
	public $lengthB;

	public function __construct($beginA, $lengthA, $beginB, $lengthB)
	{
		$this->beginA = (int) $beginA;
		$this->lengthA = (int) $lengthA;
		$this->beginB = (int) $beginB;
		$this->lengthB = (int) $lengthB;
	}

	public function getEndA()
	{
		return $this->beginA + $this->lengthA - 1;
	}

	public function getEndB()
	{
		return $this->beginB + $this->lengthB - 1;
	}

}
