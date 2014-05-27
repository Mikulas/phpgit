<?php

namespace Mikulas\PhpGit;

use PhpParser;
use PhpParser\Node\Stmt\Namespace_;


class PhpFile
{

	private $namespace = NULL;

	/** @var AClass[] */
	private $classes = [];

	public function __construct($code)
	{
		$parser = new PhpParser\Parser(new PhpParser\Lexer);

		try {
			$nodes = $parser->parse($code);

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
						$this->namespace
					);

					/** @var \PhpParser\Node\Stmt\ClassMethod $method */
					foreach ($node->getMethods() as $method)
					{
						$class->methods[] = new AMethod(
							$method->name,
							$method->getAttribute('startLine'),
							$method->getAttribute('endLine')
						);
					}

					$this->classes[] = $class;
				}
			}

		} catch (PhpParser\Error $e) {
			echo 'Parse Error: ', $e->getMessage();
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
			$gist->methods = [];
			foreach ($class->methods as $method)
			{
				// remove method { } or { } method
				// but allow neth{od } and {me}thod
				if ($method->lineTo < $start || $method->lineFrom > $end)
				{
					continue;
				}
				$gist->methods[] = $method;
			}
			$result[] = $gist;
		}

		return $result;
	}
}
