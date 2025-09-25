<?php declare(strict_types = 1);

namespace Shredio\Scraper\ScrapeOwl;

use Module\Content\Feature\FlashNews\CreateAutomaticFlashNews\Scraper\ScrapeOwlElementException;
use Shredio\Scraper\Exception\HttpClientRequestException;
use Shredio\Scraper\Exception\HttpClientTimeoutException;
use Shredio\Scraper\Exception\LogicException;
use Shredio\Scraper\Exception\ScrapeElementException;
use Shredio\Scraper\Exception\ScrapeElementNotFoundException;
use Shredio\Scraper\PageScrapeRequest;
use Shredio\Scraper\PageScrapeResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @phpstan-type ScrapeOwlElementData array{ type: non-empty-string, selector: non-empty-string, results: list<array{ text: non-empty-string, html: non-empty-string }>, error?: array{ code: non-empty-string, message: non-empty-string, suggestions?: list<non-empty-string> } }
 * @phpstan-type ScrapeOwlData array{ status: int, is_billed: bool, credits: array{ available: int, used: int, request_cost: int }, resolved_url: string, data: list<ScrapeOwlElementData>, html: string }
 */
final class ScrapeOwlResponse implements PageScrapeResponse
{

	private ?ResponseInterface $response;

	/** @var ScrapeOwlData|null */
	private ?array $data = null;

	public function __construct(
		ResponseInterface $response,
		private readonly PageScrapeRequest $request,
		private readonly ScrapeOwlPageScraper $scraper,
	)
	{
		$this->response = $response;
	}

	public function resend(): self
	{
		return $this->scraper->request($this->request);
	}

	public function isSuccess(): bool
	{
		return $this->getStatusCode() === 200;
	}

	public function getStatusCode(): int
	{
		return $this->toArray()['status'];
	}

	/**
	 * @throws HttpClientTimeoutException
	 * @throws HttpClientRequestException
	 */
	public function await(): void
	{
		if ($this->response === null) {
			return;
		}

		try {
			$this->response->getHeaders();
		} catch (HttpExceptionInterface $e) {
			throw new HttpClientRequestException($e->getMessage(), $e);
		} catch (TimeoutExceptionInterface $e) {
			throw new HttpClientTimeoutException('Timeout occurred while waiting for the HTTP response', $e);
		}
	}

	public function getElementResults(string $id): array
	{
		$array = $this->toArray();
		$element = $this->request->elements[$id] ?? throw new LogicException(sprintf('Element with ID "%s" not found.', $id));

		foreach ($array['data'] as $item) {
			if ($item['type'] === 'css' && $item['selector'] === $element->selector) {
				if (isset($item['error'])) {
					if ($item['error']['code'] === 'ELEMENT_NOT_FOUND') {
						throw new ScrapeElementNotFoundException($element->selector);
					}

					throw new ScrapeElementException(sprintf('%s: %s', $item['error']['code'], $item['error']['message']), [
						'url' => $this->request->url,
						'code' => $item['error']['code'],
						'suggestions' => $item['error']['suggestions'] ?? [],
					]);
				}
				if ($item['results'] === []) {
					throw new LogicException(sprintf('Element with ID "%s" returned no results.', $id)); // should never happen
				}

				$column = $element->html ? 'html' : 'text';
				return array_column($item['results'], $column);
			}
		}

		throw new LogicException('Element result not found in response.');
	}

	public function getElementResult(string $id): string
	{
		return implode("\n", $this->getElementResults($id));
	}

	public function getHtml(): ?string
	{
		$html = $this->toArray()['html'];
		return $html !== '' ? $html : null;
	}

	public function getPageTitleFromHtml(): ?string
	{
		$html = $this->getHtml();
		if ($html === null) {
			return null;
		}

		preg_match('/<title>(.*?)<\/title>/si', $html, $matches);
		if (isset($matches[1])) {
			$title = trim($matches[1]);
			return $title === '' ? null : $title;
		}

		return null;
	}

	/**
	 * @return ScrapeOwlData
	 */
	public function toArray(): array
	{
		if ($this->response === null) {
			if ($this->data === null) {
				throw new LogicException('Response already consumed.');
			}

			return $this->data;
		}

		/** @var ScrapeOwlData $data */
		$data = $this->response->toArray();
		$this->response = null;

		return $this->data = $data;
	}

}
