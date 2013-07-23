<?php

namespace Rixxi\Templating\DI;

use Nette;
use Nette\Utils\Validators;
use Nette\DI\Statement;
use Nette\Utils\Arrays;


class TemplateLocatorExtension extends Nette\DI\CompilerExtension
{

	private $defaults = array(
		'class' => 'detect',
		'directories' => array(
			// directory => priority,
		),
		'cache' => FALSE,
		'cacheExistingFilesOnly' => FALSE, // experimental performance optimization
	);

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$config = $this->getConfig($this->defaults);

		Validators::assertField($config, 'class', 'string');
		Validators::assertField($config, 'directories', 'array');
		Validators::assertField($config, 'cache', 'bool');
		Validators::assertField($config, 'cacheExistingFilesOnly', 'bool');

		$directories = $config['directories'];
		if (Arrays::isList($directories)) {
			$directories = array_fill_keys($directories, PHP_INT_MAX);
		}

		foreach ($this->compiler->getExtensions() as $extension) {
			if ($extension instanceof ITemplateLocatorDirectoryProvider) {
				$directories = array_merge($directories, $extension->getTemplateLocatorDirectories());
			}
		}

		$processed = array();
		foreach ($directories as $directory => $priority) {
			if (FALSE !== ($directory = realpath($directory))) {
				$processed[$directory] = $priority;
			}
		}
		$directories = $processed;

		$class = $config['class'];
		$locator = $container->addDefinition($this->prefix('locator'));

		if ('detect' === $class && count($directories) || 'priority' === $class) {
			arsort($directories, SORT_NUMERIC);
			$locator->setClass('Rixxi\Templating\TemplateLocators\PriorityTemplateLocator', array(array_keys($directories)));

		} elseif ('detect' === $class || 'default' === $class) {
			$locator->setClass('Rixxi\Templating\TemplateLocators\DefaultTemplateLocator');

		} else {
			$locator->setClass($class);
		}

		if ($config['cache']) {
			$locator->setAutowired(FALSE);

			$container->addDefinition($this->prefix('cachedLocator'))
				->setClass('Rixxi\Templating\TemplateLocators\CachedTemplateLocator', array(
					new Statement($this->prefix('@locator')),
					new Statement('@nette.cache', array('Rixxi.TemplateLocator')),
					md5(serialize($config)),
					$config['cacheExistingFilesOnly'],
				));
		}

		return $this->getConfig();
	}

}