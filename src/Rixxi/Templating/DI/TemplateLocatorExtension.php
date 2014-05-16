<?php

namespace Rixxi\Templating\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\Utils\Validators;
use Nette\Utils\Arrays;


class TemplateLocatorExtension extends CompilerExtension
{

	const TAG_COMPONENT_TEMPLATE_FACTORY = 'rixxi.component.templateFactory';

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
		$builder = $this->getContainerBuilder();

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
			if (FALSE !== ($directory = realpath($builder->expand($directory)))) {
				$processed[$directory] = $priority;
			}
		}
		$directories = $processed;

		$class = $config['class'];
		$locator = $builder->addDefinition($this->prefix('locator'));

		if ('detect' === $class && count($directories) || 'priority' === $class) {
			arsort($directories, SORT_NUMERIC);
			$locator->setClass('Rixxi\Templating\TemplateLocators\PriorityTemplateLocator', array(array_keys($directories)));

		} elseif ('detect' === $class || 'conventional' === $class) {
			$locator->setClass('Rixxi\Templating\TemplateLocators\ConventionalTemplateLocator');

		} else {
			$locator->setClass($class);
		}

		if ($config['cache']) {
			$locator->setAutowired(FALSE);

			$builder->addDefinition($this->prefix('cachedLocator'))
				->setClass('Rixxi\Templating\TemplateLocators\CachedTemplateLocator', array(
					new Statement($this->prefix('@locator')),
					2 => md5(serialize($config)),
					$config['cacheExistingFilesOnly'],
				));
		}

		$builder->addDefinition($this->prefix('controlTemplateFactory'))
			->setClass('Rixxi\Application\UI\Control\TemplateFactory')
			->setAutowired(FALSE);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		foreach (array_keys($builder->findByTag(self::TAG_COMPONENT_TEMPLATE_FACTORY)) as $service) {
			$builder->getDefinition($service)
				->addSetup('setTemplateFactory', array($this->prefix('@controlTemplateFactory')));
		}
	}

}
