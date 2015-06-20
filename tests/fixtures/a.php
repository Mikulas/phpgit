<?php

namespace Respekt\Services;

use Latte\Engine;
use Latte\Runtime\Filters;
use Nette;
use Nette\Object;
use Nette\Utils\DateTime;
use Respekt\ImageRenderer;
use Respekt\Model\Issue;
use Respekt\TOC;
use Respekt\WpCaller;
use TOC\MarkupFixer;


class HelperLoader extends Object
{

	/** @var WpCaller */
	private $wpCaller;

	/** @var ImageRenderer */
	private $imageRenderer;


	/**
	 * @param WpCaller $wpCaller
	 * @param ImageRenderer $imageRenderer
	 */
	function __construct(WpCaller $wpCaller, ImageRenderer $imageRenderer)
	{
		$this->wpCaller = $wpCaller;
		$this->imageRenderer = $imageRenderer;
	}


	public function register(Engine $engine)
	{
		$engine->addFilter(NULL, [$this, 'loader']);
		$engine->addFilter('date', [$this, 'date']); // force override
	}


	/**
	 * @param  string $helperName
	 * @param  mixed  ...$params
	 * @return mixed
	 */
	public function loader($helperName, ...$params)
	{
		return method_exists($this, $helperName) ? $this->$helperName(...$params) : NULL;
	}


	/**
	 * Parses WP Shortcodes
	 *
	 * @param  string $text
	 * @return string
	 */
	public function parseShortcodes($text)
	{
		return $this->wpCaller->do_shortcode($text);
	}


	/**
	 * @param int $postId
	 * @return bool|string
	 */
	public function attachmentUrl($postId)
	{
		return $this->wpCaller->wp_get_attachment_url($postId);
	}


	/**
	 * Formats datetime, return current datetime when param date is invalid
	 *
	 * @param string|int|DateTime $date
	 * @param string $format
	 * @return string
	 */
	public function dateOrNow($date, $format = NULL)
	{
		if (strtotime($date) > 0) {
			$date = new DateTime($date);

		} else {
			$date = new DateTime();
		}

		return Filters::date($date, $format);
	}


	/**
	 * @param int $id
	 * @param NULL $mode
	 * @return string
	 */
	public function postImage($id, $mode = NULL)
	{
		return $this->imageRenderer->getImageHtml($id, $mode);
	}


	/**
	 * 1.-4. 12. 2015
	 * 30. 2. - 3. 3. 2015
	 * 28. 12. 2015 - 2. 1. 2016
	 *
	 * @see http://prirucka.ujc.cas.cz/?id=810
	 *
	 * @param mixed $rawFrom
	 * @param mixed $rawTo
	 * @return string safe
	 */
	public function prettyRange($rawFrom, $rawTo)
	{
		$from = new DateTime($rawFrom);
		$to = new DateTime($rawTo);

		$sameYear = $from->format('Y') === $to->format('Y');
		$sameMonth = $from->format('m') === $to->format('m');

		if (!$sameYear) {
			return $from->format('j. n. Y') . ' – ' . $to->format('j. n. Y');

		} else if (!$sameMonth) {
			return $from->format('j. n.') . ' – ' . $to->format('j. n. Y');

		}

		// intentionally not spaces around dash, see @see
		return $from->format('j.') . '–' . $to->format('j. n. Y');
	}


	public function issueRange(Issue $issue)
	{
		return $this->prettyRange($issue->active_from, $issue->active_to);
	}


	/**
	 * Ads slugified ID/anchors to header tags
	 *
	 * @param string$content
	 * @return string
	 */
	public function anchorifyHeaders($content)
	{
		return (new MarkupFixer)->fix($content, TOC::TOP_LEVEL);
	}


	public function date($time, $format = NULL)
	{
		if ($format === NULL) {
			$format = 'j. n. Y';
		}

		return Filters::date($time, $format);
	}


	public function datetime($time, $format = NULL)
	{
		if ($format === NULL) {
			$format = 'j. n. Y G:i';
		}

		return Filters::date($time, $format);
	}

}
