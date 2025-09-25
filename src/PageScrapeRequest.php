<?php declare(strict_types = 1);

namespace Shredio\Scraper;

final readonly class PageScrapeRequest
{

	public bool $fullHtml;

	/**
	 * @param non-empty-string $url
	 * @param bool|null $fullHtml Whether to return the full HTML of the page. Defaults to true if no elements are specified, false otherwise.
	 * @param array<non-empty-string, ScrapeElement> $elements
	 * @param array<non-empty-string, mixed> $options
	 * @param int<1, max> $attempt The attempt number for this request (used for retries).
	 */
	public function __construct(
		public string $url,
		public bool $renderJs = false,
		?bool $fullHtml = null,
		public array $elements = [],
		public array $options = [],
		public int $attempt = 1,
	)
	{
		$this->fullHtml = $fullHtml ?? $this->elements === [];
	}

	public function withIncreasedAttempt(): self
	{
		return new self(
			url: $this->url,
			renderJs: $this->renderJs,
			fullHtml: $this->fullHtml,
			elements: $this->elements,
			options: $this->options,
			attempt: $this->attempt + 1,
		);
	}

}
