<?php


class Comparator
{

	/**
	 * @param Source[] $sourcesA
	 * @param Source[] $sourcesB
	 */
	public function compare(array $sourcesA, array $sourcesB)
	{
		$a = $this->mergeSources($sourcesA);
		$b = $this->mergeSources($sourcesB);

		$added = [];
		foreach ($a as $class) {
			foreach ($b as $compare) {
				if ($class->name === $compare->name) {
					// TODO add ClassLike wrapper with FQN and pub methods and properties
				}
			}
		}
	}


	/**
	 * @param Source[] $sources
	 * @return PhpParser\Node\Stmt\ClassLike[]
	 */
	private function mergeSources(array $sources)
	{
		/** @var PhpParser\Node\Stmt\ClassLike[] $classes */
		$classes = [];
		foreach ($sources as $source) {
			foreach ($source->getSimplified() as $class) {
				$classes[] = $class;
			}
		}

		return $classes;
	}

}
