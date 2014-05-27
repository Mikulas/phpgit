<?php

namespace Mikulas\PhpGit;


class ChangeSet
{

	/** @var array */
	public $addedClasses;

	/** @var array */
	public $addedMethods;

	/** @var array */
	public $removedClasses;

	/** @var array */
	public $removedMethods;

	/** @var array */
	public $renamedClasses;

	/** @var array */
	public $renamedMethods;

	/** @var array */
	public $changes;

	/**
	 * @param PhpFile $a
	 * @param PhpFile $b
	 * @param Edit[] $edits
	 */
	public function __construct(PhpFile $a, PhpFile $b, array $edits)
	{
		foreach ($edits as $edit)
		{
			$removed = $a->getBetweenLines($edit->beginA, $edit->getEndA());
			$added = $b->getBetweenLines($edit->beginB, $edit->getEndB());

			if (count($removed) === 1 && count($added) === 1)
			{
				$classA = $removed[0];
				$classB = $added[0];

				$containsSignatureA = $edit->beginA <= $classA->lineFrom && $edit->getEndA() >= $classA->lineFrom;
				$containsSignatureB = $edit->beginB <= $classB->lineFrom && $edit->getEndB() >= $classA->lineFrom;
				if ($classA->name !== $classB->name && $containsSignatureA && $containsSignatureB)
				{
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
						// TODO mark as change only
						continue;
					}
					foreach ($classB->methods as $methodB)
					{
						if ($methodA->name === $methodB->name)
						{
							continue 2;
						}
					}
					$this->removedMethods[] = $methodA;
				}

				foreach ($classB->methods as $methodB)
				{
					if (!$methodB->complete)
					{
						// TODO mark as change only
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
	}

}
