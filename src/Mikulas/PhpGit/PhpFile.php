<?php

namespace Mikulas\PhpGit;

use PhpParser;
use PhpParser\Node\Stmt\Namespace_;


class PhpFile
{

	public $namespace = NULL;

	/** @var AClass[] */
	public $classes = [];

	public function __construct($code)
	{
		$parser = new PhpParser\Parser(new PhpParser\Lexer);

		try {
			$nodes = $parser->parse($code);
			if (!$nodes)
			{
				return;
			}

			if ($nodes[0] instanceof Namespace_)
			{
				/** @var Namespace_ $ns */
				$ns = $nodes[0];
				$this->namespace = implode('\\', $ns->name->parts);
				$nodes = $ns->stmts;
			}

			foreach ($nodes as $node)
			{
				if ($node instanceof \PhpParser\Node\Stmt\Class_)
				{
					$class = new AClass(
						$node->name,
						$node->getAttribute('startLine'),
						$node->getAttribute('endLine'),
						$node->getDocComment(),
						$this->namespace
					);

					/** @var \PhpParser\Node\Stmt\ClassMethod $method */
					foreach ($node->getMethods() as $method)
					{
						$class->methods[] = new AMethod(
							$method->name,
							$method->getAttribute('startLine'),
							$method->getAttribute('endLine'),
							$method->getDocComment(),
							$class,
							$method->params
						);
					}

					$this->classes[] = $class;
				}
			}

		} catch (PhpParser\Error $e) {
			// echo 'Parse Error: ', $e->getMessage()  . "\n";
		}
	}

	/**
	 * @param int $start line inclusive
	 * @param int $end line inclusive
	 *
	 * @return AClass[]
	 */
	public function getBetweenLines($start, $end)
	{
		$result = [];

		foreach ($this->classes as $class)
		{
			// remove class { } or { } class
			// but allow cla{ss } and {cl}ass
			if ($class->lineTo < $start || $class->lineFrom > $end)
			{
				continue;
			}
			$gist = clone $class;
			$classCompleteFrom = $class->phpdoc ? $class->lineFrom : $class->lineFrom + 1;
			$gist->complete = $start <= $classCompleteFrom && $end >= $class->lineTo;

			list($signFrom, $signTo) = $gist->getSignatureLines();
			$gist->changedSignature = !($signTo < $start || $signFrom > $end);
			list($bodyFrom, $bodyTo) = $gist->getBodyLines();
			$gist->changedBody = !($bodyTo < $start || $bodyFrom > $end);
			$gist->linesAffected = min($end, $class->lineTo) - max($classCompleteFrom, $start) + 1;

			$gist->methods = [];
			foreach ($class->methods as $method)
			{
				// remove method { } or { } method
				// but allow neth{od } and {me}thod
				if ($method->lineTo < $start || $method->lineFrom > $end)
				{
					continue;
				}
				$methodCompleteFrom = $method->phpdoc ? $method->lineFrom : $method->lineFrom + 1;
				$method->complete = $start <= $methodCompleteFrom && $end >= $method->lineTo;
				$method->linesAffected = min($end, $method->lineTo) - max($methodCompleteFrom, $start) + 1;

				list($signFrom, $signTo) = $method->getSignatureLines();
				$method->changedSignature = !($signTo < $start || $signFrom > $end);
				list($bodyFrom, $bodyTo) = $method->getBodyLines();
				$method->changedBody = !($bodyTo < $start || $bodyFrom > $end);

				$method->class = $gist;
				$gist->methods[] = $method;
			}
			$result[] = $gist;
		}

		return $result;
	}
}
