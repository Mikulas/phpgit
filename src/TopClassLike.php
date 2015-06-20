<?php


class TopClassLike
{

	/**
	 * @var string
	 */
	protected $fqn;

	/**
	 * @var \PhpParser\Node\Stmt\ClassLike
	 */
	private $class;

	/**
	 * @var PhpParser\Node\Stmt\ClassMethod[]
	 */
	private $methods;

	/**
	 * @var PhpParser\Node\Stmt\Property[]
	 */
	private $properties;


	public function __construct(PhpParser\Node\Stmt\ClassLike $class, $namespace, array $properties, array $methods)
	{
		$this->class = $class;
		$this->fqn = $namespace . '\\' . $this->class->name;
		$this->methods = $methods;
		$this->properties = $properties;
	}

	/**
	 * @return string
	 */
	public function getFqn()
	{
		return $this->fqn;
	}


	/**
	 * @return PhpParser\Node\Stmt\ClassMethod[]
	 */
	public function getMethods()
	{
		return $this->methods;
	}


	/**
	 * @return PhpParser\Node\Stmt\Property[]
	 */
	public function getProperties()
	{
		return $this->properties;
	}

}
