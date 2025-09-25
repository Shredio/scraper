<?php declare(strict_types = 1);

namespace Shredio\Scraper\Symfony;

use Shredio\Scraper\PageScraper;
use Shredio\Scraper\ScrapeOwl\ScrapeOwlPageScraper;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class ScraperBundle extends AbstractBundle
{

	private const string SCRAPE_OWL_TYPE = 'scrape_owl';

	public function configure(DefinitionConfigurator $definition): void
	{
		$definition->rootNode() // @phpstan-ignore-line
			->children()
				->enumNode('type')
					->values([self::SCRAPE_OWL_TYPE])
					->defaultValue(self::SCRAPE_OWL_TYPE)
				->end()
				->arrayNode(self::SCRAPE_OWL_TYPE)
					->children()
						->scalarNode('secret')
							->isRequired()
							->cannotBeEmpty()
						->end()
						->integerNode('concurrencyLimit')
							->min(1)
							->defaultNull()
						->end()
					->end()
				->end()
			->end()
			->validate()
				->ifTrue(static function (array $config): bool {
					if ($config['type'] !== self::SCRAPE_OWL_TYPE) {
						return false;
					}

					return !isset($config[self::SCRAPE_OWL_TYPE]['secret']) || $config[self::SCRAPE_OWL_TYPE]['secret'] === ''; // @phpstan-ignore offsetAccess.nonOffsetAccessible
				})
				->thenInvalid('ScrapeOwl secret must be configured when using scraper type "scrape_owl".')
			->end();
	}

	/**
	 * @param array<string, mixed> $config
	 */
	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		if (($config['type'] ?? null) !== self::SCRAPE_OWL_TYPE) {
			return;
		}

		$services = $container->services();
		$serviceId = 'shredio.scraper.scrape_owl_page_scraper';

		$services->set($serviceId, ScrapeOwlPageScraper::class)
			->arg('$secret', $config[self::SCRAPE_OWL_TYPE]['secret']) // @phpstan-ignore offsetAccess.nonOffsetAccessible
			->arg('$concurrencyLimit', $config[self::SCRAPE_OWL_TYPE]['concurrencyLimit'] ?? null) // @phpstan-ignore offsetAccess.nonOffsetAccessible
			->tag('shredio.scraper.scraper');

		$services->alias(PageScraper::class, $serviceId);
	}

}
