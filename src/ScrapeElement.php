<?php declare(strict_types = 1);

namespace Shredio\Scraper;

interface ScrapeElement
{

	/**
	 * @var 'css'|'xpath' $type The type of selector (CSS or XPath)
	 */
	public string $type { get; }

	/**
	 * @var non-empty-string $selector The CSS or XPath selector to use for scraping
	 */
	public string $selector { get; }

	/**
	 * @var int<1, max>|null $timeout Time in milliseconds to wait for dynamic elements to load on the page
	 */
	public ?int $timeout { get; }
	/**
	 * Whether to return the HTML of the element or just the text content
	 */
	public bool $html { get; }

}
