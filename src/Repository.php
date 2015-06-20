<?php

use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;


class Repository
{

	/**
	 * @var string path to repository
	 */
	private $root;


	/**
	 * @param string $root path
	 */
	public function __construct($root)
	{
		if (!file_exists($root)) {
			throw new InvalidArgumentException;
		}

		$this->root = $root;
	}


	/**
	 * @param string $revSelA
	 * @param string $revSelB
	 * @return string[] paths
	 */
	public function changedFiles($revSelA, $revSelB)
	{
		$out = $this->call('diff', '--name-only', "$revSelA..$revSelB");
		return explode("\n", $out);
	}


	/**
	 * @param string $revSel (https://git-scm.com/book/en/v2/Git-Tools-Revision-Selection)
	 * @param string $file path
	 * @return string output
	 */
	public function getFileAtRevision($revSel, $file)
	{
		try {
			return $this->call('show', "$revSel:$file");

		} catch (RuntimeException $e) {
			if (FALSE !== strpos($e->getMessage(), 'exists on disk, but not in')
			 || FALSE !== strpos($e->getMessage(), 'does not exist in')) {
				return '';
			}
			throw $e;
		}
	}


	/**
	 * @param ...$args
	 * @throws RuntimeException
	 * @return string output
	 */
	private function call(...$args)
	{
		$args = array_map('escapeshellarg', $args);
		array_unshift($args, 'git');
		$cmd = implode(' ', $args);

		$process = new Process($cmd, $this->root);
		$process->setTimeout(3);
		$process->run();
		if (!$process->isSuccessful()) {
			throw new RuntimeException($process->getErrorOutput());
		}

		return $process->getOutput();
	}

}
