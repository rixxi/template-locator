<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\ComponentModel\Component;
use Nette;
use Rixxi;


class CachedTemplateLocator implements Rixxi\Templating\ITemplateLocator
{

	/**
	 * @var Rixxi\Templating\ITemplateLocator
	 */
	private $templateLocator;

	/**
	 * @var Nette\Caching\Cache
	 */
	private $filesCache;

	/**
	 * @var Nette\Caching\Cache
	 */
	private $layoutFilesCache;

	/**
	 * @var Nette\Caching\Cache
	 */
	private $componentFilesCache;

	/**
	 * @var bool
	 */
	private $onlyExistingFiles;


	/**
	 * @param Rixxi\Templating\ITemplateLocator $templateLocator
	 * @param Nette\Caching\Cache $cache
	 * @param string|NULL $setupFingerprint
	 * @param bool $onlyExistingFiles
	 */
	public function __construct(Rixxi\Templating\ITemplateLocator $templateLocator, Nette\Caching\Cache $cache, $setupFingerprint = NULL, $onlyExistingFiles = FALSE)
	{
		$this->templateLocator = $templateLocator;
		if ($setupFingerprint !== $cache['setupFingerprint']) {
			$cache->clean(array(Cache::ALL => TRUE));
			$cache['setupFingerprint'] = $setupFingerprint;
		}
		$this->filesCache = $cache->derive('files');
		$this->layoutFilesCache = $cache->derive('layoutFiles');
		$this->componentFilesCache = $cache->derive('componentFiles');
		$this->onlyExistingFiles = $onlyExistingFiles;
	}


	public function formatLayoutTemplateFiles(Presenter $presenter)
	{
		if (NULL === $this->layoutFilesCache[$name = $presenter->getName()]) {
			$templateLocator = $this->templateLocator;
			$onlyExistingFiles = $this->onlyExistingFiles;
			return $this->layoutFilesCache->save($name, function () use ($presenter, $templateLocator, $onlyExistingFiles) {
				$list = $templateLocator->formatLayoutTemplateFiles($presenter);
				if ($onlyExistingFiles) {
					$list = array_filter($list, 'is_file');
				}

				return $list;
			});
		}

		return $this->layoutFilesCache[$name];
	}


	public function formatTemplateFiles(Presenter $presenter)
	{
		if (NULL === $this->filesCache[$name = $presenter->getName()]) {
			$templateLocator = $this->templateLocator;
			$onlyExistingFiles = $this->onlyExistingFiles;
			return $this->filesCache->save($name, function () use ($presenter, $templateLocator, $onlyExistingFiles) {
				$list = $templateLocator->formatTemplateFiles($presenter);
				if ($onlyExistingFiles) {
					$list = array_filter($list, 'is_file');
				}

				return $list;
			});
		}

		return $this->filesCache[$name];
	}


	public function formatComponentTemplateFiles(Component $component, $view = 'default')
	{
		$hash = $component->getPresenter()->getName() . '|' . $component->getReflection()->getShortName() . '|' . $view;
		if (NULL === $this->componentFilesCache[$hash]) {
			$templateLocator = $this->templateLocator;
			$onlyExistingFiles = $this->onlyExistingFiles;

			return $this->componentFilesCache->save($hash, function () use ($component, $view, $templateLocator, $onlyExistingFiles) {
				$list = $templateLocator->formatComponentTemplateFiles($component, $view);
				if ($onlyExistingFiles) {
					$list = array_filter($list, 'is_file');
				}

				return $list;
			});
		}

		return $this->componentFilesCache[$hash];
	}
}
