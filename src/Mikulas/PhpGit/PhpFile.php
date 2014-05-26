<?php

namespace Mikulas\PhpGit;

use PhpParser;
use PhpParser\Node\Stmt\Namespace_;


class PhpFile
{

	private $namespace = NULL;

	/** @var AClass[] */
	private $clases = [];

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
						$node->getAttribute('endLine')
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

					$this->clases[] = $class;
				}
			}

		} catch (PhpParser\Error $e) {
			echo 'Parse Error: ', $e->getMessage();
		}
	}
}
