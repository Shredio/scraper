<?php declare(strict_types = 1);

namespace Shredio\Scraper\Exception;

use Throwable;

final class ScrapeElementException extends RuntimeException
{

	/**
	 * @param array<non-empty-string, mixed> $details
	 */
	public function __construct(
		string $message,
		public readonly array $details = [],
		?Throwable $previous = null,
	)
	{
		parent::__construct($message, $previous);
	}

}
