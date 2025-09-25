<?php declare(strict_types = 1);

namespace Shredio\Scraper\Exception;

use Throwable;

final class ScrapeElementNotFoundException extends RuntimeException
{

	/**
	 * @param non-empty-string $selector
	 */
	public function __construct(string $selector, ?Throwable $previous = null)
	{
		parent::__construct(sprintf('No elements found for selector "%s".', $selector), $previous);
	}

}
