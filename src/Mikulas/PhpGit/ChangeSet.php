<?php

namespace Mikulas\PhpGit;


use Exception;


class ChangeSet
{

	/** @var AClass[] */
	public $addedClasses = [];

	/** @var AMethod[] */
	public $addedMethods = [];

	/** @var AClass[] */
	public $removedClasses = [];

	/** @var AMethod[] */
	public $removedMethods = [];

	/** @var AClass[][] */
	public $renamedClasses = [];

	/** @var AMethod[][] */
	public $renamedMethods = [];

	/** @var AMethod[][] */
	public $changedMethods = [];

	/** @var AMethod[][] */
	public $changedMethodParameters = [];

	/**
	 * @param PhpFile|NULL $a
	 * @param PhpFile|NULL $b
	 * @param Edit[] $edits
	 *
	 * @throws Exception
	 */
	public function __construct($a, $b, array $edits)
	{
		foreach ($edits as $edit)
		{
			$removed = $a ? $a->getBetweenLines($edit->beginA, $edit->getEndA()) : [];
			$added = $b ? $b->getBetweenLines($edit->beginB, $edit->getEndB()) : [];

			if ($a && $b && $a->namespace !== $b->namespace)
			{
				if (count($a->classes) !== count($b->classes))
				{
					dump($a, $b, $edit);
					throw new \Exception('not implemented');
				}
				foreach ($a->classes as $i => $class)
				{
					$this->renamedClasses[] = [$class, $b->classes[$i]];
				}
			}

			if (count($removed) === 1 && count($added) === 1)
			{
				$classA = $removed[0];
				$classB = $added[0];

				if ($classA->name !== $classB->name && !$classA->changedBody && !$classB->changedBody)
				{
					$this->renamedClasses[] = [$classA, $classB];
				}

				if (count($classA->methods) === 1 && count($classB->methods) === 1)
				{
					$methodA = $classA->methods[0];
					$methodB = $classB->methods[0];
					if ($methodA->name !== $methodB->name && !$methodA->changedBody && !$methodB->changedBody)
					{
						$this->renamedMethods[] = [$methodA, $methodB];
					}
					else if (($methodA->changedSignature || $methodB->changedSignature)
						&& !$methodA->changedBody && !$methodB->changedBody)
					{
						if ($methodA->getParamSignatureWithoutNames()
						!== $methodB->getParamSignatureWithoutNames())
						{
							$this->changedMethodParameters[] = [$methodA, $methodB];
						}
					}
					else if (($methodA->changedBody || $methodB->changedBody)
						&& !$methodA->changedSignature && !$methodB->changedSignature)
					{
						$this->changedMethods[] = $methodB;
					}
					else
					{
						// TODO mark this as changed both signature and body
						$this->changedMethods[] = $methodB;
					}
				}

				foreach ($classA->methods as $methodA)
				{
					if (!$methodA->complete)
					{
						$this->changedMethods[] = $methodA;
						continue;
					}
					foreach ($classB->methods as $methodB)
					{
						if ($methodA->name === $methodB->name)
						{
							// TODO what case is this
							continue 2;
						}
					}
					$this->removedMethods[] = $methodA;
				}

				foreach ($classB->methods as $methodB)
				{
					if (!$methodB->complete)
					{
						$this->changedMethods[] = $methodB;
						continue;
					}
					foreach ($classA->methods as $methodA)
					{
						if ($methodB->name === $methodA->name)
						{
							continue 2;
						}
					}
					$this->addedMethods[] = $methodB;
				}
			}
			else
			{
				foreach ($added as $addedClass)
				{
					if (!$addedClass->complete)
					{
						continue;
					}
					$this->addedClasses[] = $addedClass;
				}

				foreach ($removed as $removedClass)
				{
					if (!$removedClass->complete)
					{
						continue;
					}
					$this->removedClasses[] = $removedClass;
				}
			}
		}

		$signatures = [];
		foreach ($this->changedMethods as $i => $method)
		{
			foreach ($this->addedMethods as $added)
			{
				if ((string) $method === (string) $added)
				{
					unset($this->changedMethods[$i]);
					continue 2;
				}
			}
			foreach ($this->removedMethods as $removed)
			{
				if ((string) $method === (string) $removed)
				{
					unset($this->changedMethods[$i]);
					continue 2;
				}
			}
			foreach ($this->renamedMethods as $node)
			{
				list($a, $b) = $node;
				if ((string) $method === (string) $a || (string) $method === (string) $b)
				{
					unset($this->changedMethods[$i]);
					continue 2;
				}
			}
			foreach ($this->changedMethodParameters as $node)
			{
				list($a, $b) = $node;
				if ((string) $method === (string) $a || (string) $method === (string) $b)
				{
					unset($this->changedMethods[$i]);
					continue 2;
				}
			}

			if (isset($signatures[$method->name]))
			{
				unset($this->changedMethods[$i]);
				continue;
			}
			$signatures[$method->name] = TRUE;
		}
	}

	/**
	 * @return bool
	 */
	public function containsChange()
	{
		return $this->addedClasses
			|| $this->addedMethods
			|| $this->removedClasses
			|| $this->removedMethods
			|| $this->renamedClasses
			|| $this->renamedMethods
			|| $this->changedMethods
			|| $this->changedMethodParameters;
	}

}
