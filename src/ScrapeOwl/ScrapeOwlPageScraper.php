<?php declare(strict_types = 1);

namespace Shredio\Scraper\ScrapeOwl;

use SensitiveParameter;
use Shredio\Scraper\Exception\LogicException;
use Shredio\Scraper\PageScraper;
use Shredio\Scraper\PageScrapeRequest;
use Shredio\Scraper\PageScrapeResponse;
use Shredio\Scraper\ScrapeElement;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

/**
 * @phpstan-type UsageType array{ credits: int, credits_used: int, requests: int, failed_requests: int, successful_requests: int, concurrency_limit: int, concurrent_requests: int }
 */
final readonly class ScrapeOwlPageScraper implements PageScraper
{

	private RetryableHttpClient $httpClient;

	public function __construct(
		#[SensitiveParameter]
		private string $secret,
		private ?int $concurrencyLimit = null,
	)
	{
		if ($this->concurrencyLimit !== null && $this->concurrencyLimit < 1) {
			throw new LogicException('Configured concurrency limit must be a positive integer.');
		}
		$this->httpClient = new RetryableHttpClient(HttpClient::create());
	}

	public function request(PageScrapeRequest $request): ScrapeOwlResponse
	{
		$requestJson = [
			'api_key' => $this->secret,
			'url' => $request->url,
			'render_js' => $request->renderJs,
			'html' => $request->fullHtml,
			'json_response' => true,
		];
		if ($request->elements !== []) {
			$requestJson['elements'] = [];
			foreach ($request->elements as $element) {
				$requestJson['elements'][] = $this->elementToArray($element);
			}
		}

		$response = $this->httpClient->request('POST', 'https://api.scrapeowl.com/v1/scrape', [
			'json' => $requestJson,
		]);
		return new ScrapeOwlResponse($response, $request, $this);
	}

	/**
	 * @param list<PageScrapeRequest> $requests
	 * @return iterable<int, PageScrapeResponse>
	 */
	public function batchRequests(array $requests): iterable
	{
		foreach (array_chunk($requests, $this->getConcurrencyLimit()) as $batch) {
			$responses = [];
			foreach ($batch as $request) {
				$responses[] = $this->request($request);
			}
			yield from $responses;
		}
	}

	/**
	 * @return int<1, max>
	 */
	private function getConcurrencyLimit(): int
	{
		if ($this->concurrencyLimit !== null) {
			return $this->concurrencyLimit;
		}

		return max($this->getUsage()['concurrency_limit'], 1);
	}

	/**
	 * @return UsageType
	 */
	private function getUsage(): array
	{
		$response = $this->httpClient->request('GET', 'https://api.scrapeowl.com/v1/usage', [
			'query' => [
				'api_key' => $this->secret,
			],
		]);

		/** @var UsageType */
		return $response->toArray();
	}

	/**
	 * @return mixed[]
	 */
	private function elementToArray(ScrapeElement $element): array
	{
		$values = [
			'type' => $element->type,
			'selector' => $element->selector,
		];
		if ($element->timeout !== null) {
			$values['timeout'] = $element->timeout;
		}
		if ($element->html) {
			$values['html'] = true;
		}

		return $values;
	}

}
