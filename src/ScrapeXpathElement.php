<?php declare(strict_types = 1);

namespace Shredio\Scraper;

final readonly class ScrapeXpathElement implements ScrapeElement
{

	/** @var 'xpath' */
	public string $type;

	/**
	 * @param non-empty-string $selector
	 * @param int<1, max>|null $timeout in milliseconds
	 * @param bool $html Whether to return the HTML of the element or just the text content
	 */
	public function __construct(
		public string $selector,
		public ?int $timeout = null,
		public bool $html = false,
	)
	{
		$this->type = 'xpath';
	}

}
