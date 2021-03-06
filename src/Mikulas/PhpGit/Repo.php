<?php

namespace Mikulas\PhpGit;

use Symfony\Component\Process\Process;


class Repo
{

	/** @var string path */
	protected $dir;

	/** @var string char */
	private $delim = "\xB";

	public function __construct($dir)
	{
		if (!file_exists($dir))
		{
			throw new \Exception; // TODO
		}
		$this->dir = $dir;
	}

	public function getCommits()
	{
		$commits = [];
		$out = $this->run('log --pretty=format:%s', escapeshellarg("%H{$this->delim}%at{$this->delim}%aE{$this->delim}%s"));
		foreach (explode("\n", $out) as $line)
		{
			$commits[] = explode($this->delim, $line);
		}
		return $commits;
	}

	public function getCommitChanges($hash)
	{
		$out = $this->run('show -U0 --find-renames %s', $hash);

		$files = [];
		$file = NULL;
		$isCopy = FALSE;
		foreach (explode("\n", $out) as $line)
		{
			if (strpos($line, 'diff --git') === 0)
			{
				if ($file)
				{
					if ($isCopy)
					{
						$file['fileA'] = NULL;
					}
					$files[] = $file;
				}
				$file = [
					'fileA' => NULL,
					'fileB' => NULL,
					'edits' => [],
				];
			}
			else if (strpos($line, 'copy from') === 0)
			{
				$isCopy = TRUE;
			}
			else if (strpos($line, '--- ') === 0)
			{
				$file['fileA'] = strpos($line, '--- /dev/null') === 0 ? NULL : substr($line, 6);
			}
			else if (strpos($line, '+++ b/') === 0)
			{
				$file['fileB'] = substr($line, 6);
			}
			else if (strpos($line, '@@ ') === 0)
			{
				list($tmp, $from, $to) = explode(' ', $line);
				list($beginA, $lengthA) = explode(',', $from) + [NULL, 1];
				list($beginB, $lengthB) = explode(',', $to) + [NULL, 1];
				$file['edits'][] = new Edit(abs($beginA), $lengthA, $beginB, $lengthB);
			}
		}

		if ($isCopy)
		{
			$file['fileA'] = NULL;
		}
		$files[] = $file;

		return $files;
	}

	public function getAuthors()
	{
		$out = $this->run('log --format=%s | sort -u', escapeshellarg("%aE{$this->delim}%aN"));
		$authors = [];
		foreach (explode("\n", $out) as $line)
		{
			if (!$line)
			{
				continue;
			}
			list($email, $name) = explode($this->delim, $line);
			$authors[$email] = $name;
		}
		return $authors;
	}

	public function getFile($revision, $file)
	{
		return $this->run('show %s:%s', $revision, $file);
	}

	/**
	 * @param string $pattern for sprintf
	 * @param mixed $arg1 for sprintf ...
	 *
	 * @return string;
	 */
	protected function run($pattern, $arg1 = NULL)
	{
		$args = func_get_args();
		$format = 'git --git-dir %s --work-tree %s ' . array_shift($args);
		array_unshift($args, escapeshellarg($this->dir));
		array_unshift($args, escapeshellarg($this->dir . '/.git'));
		array_unshift($args, $format);
		$cmd = call_user_func_array('sprintf', $args);

		$p = new Process($cmd);
		$p->run();
		return $p->getOutput();
	}

}
