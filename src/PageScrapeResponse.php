<?php declare(strict_types = 1);

namespace Shredio\Scraper;

use Shredio\Scraper\Exception\HttpClientRequestException;
use Shredio\Scraper\Exception\HttpClientTimeoutException;
use Shredio\Scraper\Exception\ScrapeElementException;
use Shredio\Scraper\Exception\ScrapeElementNotFoundException;

interface PageScrapeResponse
{

	public function isSuccess(): bool;

	public function resend(): PageScrapeResponse;

	/**
	 * Waits for the scraping process to complete.
	 *
	 * @throws HttpClientTimeoutException
	 * @throws HttpClientRequestException
	 */
	public function await(): void;

	/**
	 * @param non-empty-string $id
	 * @return non-empty-list<non-empty-string>
	 *
	 * @throws ScrapeElementException
	 * @throws ScrapeElementNotFoundException
	 */
	public function getElementResults(string $id): array;

	/**
	 * @param non-empty-string $id
	 * @return non-empty-string
	 *
	 * @throws ScrapeElementException
	 * @throws ScrapeElementNotFoundException
	 */
	public function getElementResult(string $id): string;

	/**
	 * @return non-empty-string|null
	 */
	public function getHtml(): ?string;

	/**
	 * @return non-empty-string|null
	 */
	public function getPageTitleFromHtml(): ?string;

}
