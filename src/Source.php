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
	 * @return PhpParser\Node\Stmt\ClassLike[]
	 */
	public function getSimplified()
	{
		/** @var PhpParser\Node\Stmt\ClassLike $classes */
		$classes = [];

		foreach ($this->tree as $node) {
			foreach ($this->findClassLike($node) as $class) {
				$classes[] = $this->simplifyClass($class);
			}
		}

		return $classes;
	}


	/**
	 * @param PhpParser\Node $node
	 * @return PhpParser\Node\Stmt\ClassLike[]
	 */
	private function findClassLike(PhpParser\Node $node)
	{
		if ($node instanceof PhpParser\Node\Stmt\ClassLike) {
			yield $node;

		} else {
			if ($node instanceof PhpParser\Node\Stmt\Namespace_) {
				foreach ($node->stmts as $subNode) {
					foreach ($this->findClassLike($subNode) as $class) {
						yield $class;
					}
				}
			}
		}
	}


	/**
	 * @param PhpParser\Node\Stmt\ClassLike $class
	 * @return PhpParser\Node\Stmt\ClassLike $class
	 */
	private function simplifyClass(PhpParser\Node\Stmt\ClassLike $class)
	{
		$class->properties = [];
		foreach ($this->findProperties($class) as $property) {
			$class->properties[] = $property;
		}

		$class->methods = [];
		foreach ($this->findMethods($class) as $method) {
			$class->methods[] = $method;
		}

		return $class;
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
