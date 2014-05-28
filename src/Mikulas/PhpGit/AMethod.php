<?php

namespace Mikulas\PhpGit;


use Nette\Reflection\Annotation;
use Nette\Reflection\AnnotationsParser;
use PhpParser\Comment\Doc;
use PhpParser\Node\Param;


class AMethod extends CodeBlock
{
	/** @var AClass */
	public $class;

	/** @var array */
	public $params;

	/** @var Doc */
	public $phpdoc;

	public function __construct($name, $lineFrom, $lineTo, AClass $class, array $params, $phpdoc)
	{
		parent::__construct($name, $lineFrom, $lineTo);
		$this->class = $class;
		$this->params = $params;
		$this->phpdoc = $phpdoc;
	}

	/**
	 * @return int[] begin end
	 */
	public function getSignatureLines()
	{
		/** @var Param[] $p */
		$p = $this->params;
		$from = $this->phpdoc ? $this->phpdoc->getLine() : $this->lineFrom;
		if (count($p) === 0)
		{
			return [$from, $this->lineTo];
		}

		return [$from, max($p)->getAttribute('endLine')];
	}

	public function __toString()
	{
		$ps = $this->getParamSignature();
		return "{$this->class}::{$this->name}($ps)";
	}

	public function getParamSignature()
	{
		$params = [];
		foreach ($this->getTypedParams() as $name => $type)
		{
			$params[] = ltrim("$type \$$name");
		}
		return implode(', ', $params);
	}

	public function getTypedParams()
	{
		$typed = [];
		$types = $this->parseAnnotation();

		/** @var Param $param */
		foreach ($this->params as $i => $param)
		{
			$type = isset($types[$param->name]) ? $types[$param->name] :
				(isset($types[$i]) ? $types[$i] : $param->type);
			$typed[$param->name] = $type;
		}

		return $typed;
	}

	public function parseAnnotation()
	{
		if (!$this->phpdoc)
		{
			return [];
		}

		$types = [];
		foreach (explode("\n", $this->phpdoc->getText()) as $line)
		{
			$match = [];
			if (preg_match('~@param\s+((?P<type>\S+)\s+)?(\$?(?P<name>\S+))~', $line, $match))
			{
				if (!$match['type'] && $match['name'])
				{
					// * @param Exception
					$types[] = $match['name'];
				}
				else
				{
					// * @param Exception $exp
					$types[$match['name']] = $match['type'];
				}
			}
		}

		return $types;
	}

}
