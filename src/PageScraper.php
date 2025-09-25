<?php declare(strict_types = 1);

namespace Shredio\Scraper;

use Shredio\Scraper\ScrapeOwl\ScrapeOwlResponse;

interface PageScraper
{

	public function request(PageScrapeRequest $request): ScrapeOwlResponse;

	/**
	 * @param list<PageScrapeRequest> $requests
	 * @return iterable<int, PageScrapeResponse>
	 */
	public function batchRequests(array $requests): iterable;

}
