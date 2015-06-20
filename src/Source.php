<?php

class Source
{

	/**
	 * @var PhpParser\Node[]
	 */
	protected $tree;


	/**
	 * @throws PhpParser\Error
	 */
	public function __construct(PhpParser\Parser $parser, $content)
	{
		$this->tree = $parser->parse($content);
	}


	/**
	 * @return TopClassLike[]
	 */
	public function getSimplified()
	{
		/** @var TopClassLike[] $classes */
		$classes = [];

		foreach ($this->tree as $node) {
			foreach ($this->findClassLike($node) as list($class, $namespace)) {
				$classes[] = $this->simplifyClass($class, $namespace);
			}
		}

		return $classes;
	}


	/**
	 * @param PhpParser\Node $node
	 * @return array [PhpParser\Node\Stmt\ClassLike, string namespace]
	 */
	private function findClassLike(PhpParser\Node $node)
	{
		if ($node instanceof PhpParser\Node\Stmt\ClassLike) {
			yield [$node, ''];

		} else {
			if ($node instanceof PhpParser\Node\Stmt\Namespace_) {
				foreach ($node->stmts as $subNode) {
					foreach ($this->findClassLike($subNode) as list($class, $_)) {
						yield [$class, $node->name];
					}
				}
			}
		}
	}


	/**
	 * @param PhpParser\Node\Stmt\ClassLike $class
	 * @param string $namespace
	 * @return TopClassLike $class
	 */
	private function simplifyClass(PhpParser\Node\Stmt\ClassLike $class, $namespace)
	{
		$properties = [];
		foreach ($this->findProperties($class) as $property) {
			$properties[] = $property;
		}

		$methods = [];
		foreach ($this->findMethods($class) as $method) {
			$methods[] = $method;
		}

		return new TopClassLike($class, $namespace, $properties, $methods);
	}


	/**
	 * @param PhpParser\Node\Stmt\ClassLike $class
	 * @return PhpParser\Node\Stmt\Property[]
	 */
	private function findProperties(PhpParser\Node\Stmt\ClassLike $class)
	{
		if ($class instanceof PhpParser\Node\Stmt\Trait_
			|| $class instanceof PhpParser\Node\Stmt\Class_
		) {
			foreach ($class->stmts as $node) {
				if ($node instanceof \PhpParser\Node\Stmt\Property && $node->isPublic()) {
					yield $node;
				}
			}
		}
	}


	/**
	 * @param PhpParser\Node\Stmt\ClassLike $class
	 * @return PhpParser\Node\Stmt\ClassMethod[]
	 */
	private function findMethods(PhpParser\Node\Stmt\ClassLike $class)
	{
		/** @var PhpParser\Node\Stmt\ClassMethod $method */
		foreach ($class->getMethods() as $method) {
			if ($method->name === '__construct') {
				continue;
			}

			if ($method->isPublic()) {
				yield $method;
			}
		}
	}

}
