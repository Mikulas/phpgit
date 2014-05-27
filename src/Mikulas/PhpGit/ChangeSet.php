<?php

namespace Mikulas\PhpGit;


class ChangeSet
{

	/** @var array */
	public $addedClasses = [];

	/** @var array */
	public $addedMethods = [];

	/** @var array */
	public $removedClasses = [];

	/** @var array */
	public $removedMethods = [];

	/** @var array */
	public $renamedClasses = [];

	/** @var array */
	public $renamedMethods = [];

	/** @var array */
	public $changedMethods = [];

	/**
	 * @param PhpFile|NULL $a
	 * @param PhpFile|NULL $b
	 * @param Edit[] $edits
	 */
	public function __construct($a, $b, array $edits)
	{
		foreach ($edits as $edit)
		{
			$removed = $a ? $a->getBetweenLines($edit->beginA, $edit->getEndA()) : [];
			$added = $b ? $b->getBetweenLines($edit->beginB, $edit->getEndB()) : [];

			if (count($removed) === 1 && count($added) === 1)
			{
				$classA = $removed[0];
				$classB = $added[0];

				$classRenamed = FALSE;
				$containsSignatureA = $edit->beginA <= $classA->lineFrom && $edit->getEndA() >= $classA->lineFrom;
				$containsSignatureB = $edit->beginB <= $classB->lineFrom && $edit->getEndB() >= $classA->lineFrom;
				if ($classA->name !== $classB->name && $containsSignatureA && $containsSignatureB)
				{
					$classRenamed = TRUE;
					$this->renamedClasses[] = [$classA, $classB];
				}

				if (count($classA->methods) === 1 && count($classB->methods) === 1)
				{
					$methodA = $classA->methods[0];
					$methodB = $classB->methods[0];
					if ($methodA->name !== $methodB->name)
					{
						$this->renamedMethods[] = [$methodA, $methodB];
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
				if ($method === $added)
				{
					unset($this->changedMethods[$i]);
					continue 2;
				}
			}
			foreach ($this->removedMethods as $removed)
			{
				if ($method === $removed)
				{
					unset($this->changedMethods[$i]);
					continue 2;
				}
			}
			foreach ($this->renamedMethods as $node)
			{
				list($a, $b) = $node;
				if ($method === $a || $method === $b)
				{
					unset($this->changedMethods[$i]);
					continue 2;
				}
			}

			if (isset($signatures[(string) $method]))
			{
				unset($this->changedMethods[$i]);
				continue;
			}
			$signatures[(string) $method] = TRUE;
		}
	}

}
