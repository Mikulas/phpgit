<?php

namespace Mikulas\PhpGit;


use Nette\Reflection\Annotation;
use Nette\Reflection\AnnotationsParser;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr;
use PhpParser\Node\Param;


class AMethod extends CodeBlock
{
	/** @var AClass */
	public $class;

	/** @var array */
	public $params;

	/** @var Doc */
	public $phpdoc;

	public function __construct($name, $lineFrom, $lineTo, $phpdoc, AClass $class, array $params)
	{
		parent::__construct($name, $lineFrom, $lineTo, $phpdoc);
		$this->class = $class;
		$this->params = $params;
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

	/**
	 * @return int[] begin end
	 */
	public function getBodyLines()
	{
		/** @var Param[] $p */
		$p = $this->params;
		$from = $this->phpdoc ? $this->phpdoc->getLine() : $this->lineFrom;
		if (count($p) === 0)
		{
			return [$this->lineFrom + 1, $this->lineTo];
		}

		return [max($p)->getAttribute('endLine') + 1, $this->lineTo];
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
			$params[] = ltrim("$type->type \$$name" . ($type->default ? ' = ' . $type->default : ''));
		}
		return implode(', ', $params);
	}

	public function getParamDefaults(Param $param)
	{
		if (!$param->default)
		{
			return NULL;
		}

		$printer = new \PhpParser\PrettyPrinter\Standard;
		$code = $printer->prettyPrint([$param->default]);
		return rtrim($code, ';');
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
			$typed[$param->name] = (object) [
				'type' => $type,
				'default' => $this->getParamDefaults($param),
			];
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
