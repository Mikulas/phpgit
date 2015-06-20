<?php


class Comparator
{

	const PHP_VARIABLE = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';

	/**
	 * @param Source[] $sourcesA
	 * @param Source[] $sourcesB
	 */
	public function compare(array $sourcesA, array $sourcesB)
	{
		$a = $this->mergeSources($sourcesA);
		$b = $this->mergeSources($sourcesB);

		list($classesAdded, $classesRemoved) = $this->classesAddedRemoved($a, $b);
		list($methodsAdded, $methodsRemoved) = $this->methodsAddedRemoved($a, $b);

		$removed = array_merge($classesRemoved, $methodsRemoved);
		$added = array_merge($classesAdded, $methodsAdded);

		asort($removed);
		asort($added);

		foreach ($removed as $line) {
			echo "- $line\n";
		}
		foreach ($added as $line) {
			echo "+ $line\n";
		}
	}


	/**
	 * @param Source[] $sources
	 * @return TopClassLike[]
	 */
	private function mergeSources(array $sources)
	{
		/** @var TopClassLike[] $classes */
		$classes = [];
		foreach ($sources as $source) {
			foreach ($source->getSimplified() as $class) {
				$classes[] = $class;
			}
		}

		return $classes;
	}


	/**
	 * @param TopClassLike[] $a
	 * @param TopClassLike[] $b
	 * @return array fqn class names
	 */
	private function classesAddedRemoved($a, $b)
	{
		/** @var TopClassLike[] $added */
		$added = [];
		foreach ($b as $class) {
			$found = FALSE;
			foreach ($a as $compare) {
				if ($class->getFqn() === $compare->getFqn()) {
					$found = TRUE;
					break;
				}
			}
			if (!$found) {
				$added[] = $class->getFqn();
			}
		}

		/** @var TopClassLike[] $removed */
		$removed = [];
		foreach ($a as $class) {
			$found = FALSE;
			foreach ($b as $compare) {
				if ($class->getFqn() === $compare->getFqn()) {
					$found = TRUE;
					break;
				}
			}
			if (!$found) {
				$removed[] = $class->getFqn();
			}
		}

		asort($added);
		asort($removed);

		return [$added, $removed];
	}


	/**
	 * @param TopClassLike[] $a
	 * @param TopClassLike[] $b
	 * @return array
	 */
	private function methodsAddedRemoved($a, $b)
	{
		$methodsA = $this->getMethods($a);
		$methodsB = $this->getMethods($b);

		$removed = [];
		foreach ($methodsA as $method) {
			$found = FALSE;
			foreach ($methodsB as $compare) {
				if ($method->fqn === $compare->fqn) {
					$found = TRUE;
					break;
				}
			}
			if (!$found) {
				$removed[] = $this->formatMethod($method);
			}
		}

		$added = [];
		foreach ($methodsB as $method) {
			$found = FALSE;
			foreach ($methodsA as $compare) {
				if ($method->fqn === $compare->fqn) {
					$found = TRUE;
					break;
				}
			}
			if (!$found) {
				$added[] = $this->formatMethod($method);
			}
		}

		return [$added, $removed];
	}


	private function formatMethod(PhpParser\Node\Stmt\ClassMethod $method)
	{
		$args = [];

		$types = $this->parseDoc($method->getDocComment());
		foreach ($method->params as $param) {
			$type = $param->type;
			if (array_key_exists($param->name, $types)) {
				$type = $types[$param->name];
			}
			$args[] = ltrim($type . ' $' . $param->name);
		}
		return $method->fqn . '(' . implode(', ', $args) . ')';
	}


	private function parseDoc($doc)
	{
		$types = [];

		$matches = [];
		preg_match_all('~@param\s+(?P<param>.*?)$~m', $doc, $matches);
		foreach ($matches['param'] as $line) {
			$match = [];

			if (preg_match('~(?P<type>[^, ]+)\s+\$(?P<var>' . self::PHP_VARIABLE . ')~', $line, $match)) {
				$types[$match['var']] = $match['type'];

			} else if (preg_match('~\$(?P<var>' . self::PHP_VARIABLE . ')\s+(?P<type>[^, ]+)~', $line, $match)) {
				$types[$match['var']] = $match['type'];
			}
		}

		return $types;
	}


	/**
	 * @param TopClassLike[] $list
	 * @return PhpParser\Node\Stmt\ClassMethod[] fqn method names
	 */
	private function getMethods($list)
	{
		$methods = [];
		foreach ($list as $class) {
			foreach ($class->getMethods() as $method) {
				$method->fqn = $class->getFqn() . '::' . $method->name;
				$methods[] = $method;
			}
		}

		return $methods;
	}

}
