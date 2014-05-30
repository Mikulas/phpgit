<?php

namespace Mikulas\PhpGit;


use Exception;


class ComposerUpdate
{

	/** @var array */
	public $removed = [];

	/** @var array */
	public $added = [];

	/** @var array */
	public $updated = [];

	/**
	 * @param string $lockA content
	 * @param string $lockB content
	 */
	public function __construct($lockA, $lockB)
	{
		$a = json_decode($lockA, TRUE);
		$b = json_decode($lockB, TRUE);

		$old = $this->simplify($a);
		$new = $this->simplify($b);
		file_put_contents(__DIR__ . '/a', $lockA);
		file_put_contents(__DIR__ . '/b', $lockB);

		foreach ($old as $name => $version)
		{
			if (!isset($new[$name]))
			{
				$this->removed[] = $name;
			}
			elseif ($version !== $new[$name])
			{
				$this->updated[$name] = [$version, $new[$name]];
			}
		}
		foreach ($new as $name => $version)
		{
			if (!isset($old[$name]))
			{
				$this->added[] = $name;
			}
		}
	}

	/**
	 * @param array $lockfile
	 *
	 * @return array
	 */
	private function simplify($lockfile)
	{
		$libs = [];
		foreach ($lockfile['packages'] as $package)
		{
			$version = $package['version'];
			if ($version === 'dev-master')
			{
				$version .= '#' . substr($package['source']['reference'], 0, 7);
			}
			$libs[$package['name']] = $version;
		}

		return $libs;
	}
}
