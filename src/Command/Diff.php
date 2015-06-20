<?php


class Diff
{

	/**
	 * @var Repository
	 */
	private $repo;

	/**
	 * @var Comparator
	 */
	private $comparator;

	/**
	 * @var PhpParser\Parser
	 */
	private $parser;


	public function __construct(Repository $repo, Comparator $comparator, PhpParser\Parser $parser)
	{
		$this->repo = $repo;
		$this->comparator = $comparator;
		$this->parser = $parser;
	}


	public function compare($revSelA, $revSelB)
	{
		$files = $this->repo->changedFiles($revSelA, $revSelB);

		$sourcesA = [];
		$sourcesB = [];

		foreach ($files as $file) {
			$raw = $this->repo->getFileAtRevision($revSelA, $file);
			$sourcesA[] = new Source($this->parser, $raw);

			$raw = $this->repo->getFileAtRevision($revSelB, $file);
			$sourcesB[] = new Source($this->parser, $raw);
		}

		$this->comparator->compare($sourcesA, $sourcesB);
	}

}
