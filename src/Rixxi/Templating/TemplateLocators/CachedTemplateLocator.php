<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
use Nette\Caching\IStorage as ICachingStorage;
use Nette\Caching\Cache;
use Nette\ComponentModel\Component;
use Rixxi\Templating\ITemplateLocator;


class CachedTemplateLocator implements ITemplateLocator
{

	/**
	 * @var ITemplateLocator
	 */
	private $templateLocator;

	/**
	 * @var Cache
	 */
	private $filesCache;

	/**
	 * @var Cache
	 */
	private $layoutFilesCache;

	/**
	 * @var Cache
	 */
	private $componentFilesCache;

	/**
	 * @var bool
	 */
	private $onlyExistingFiles;


	/**
	 * @param ITemplateLocator
	 * @param Cache
	 * @param string|null
	 * @param bool
	 */
	public function __construct(ITemplateLocator $templateLocator, ICachingStorage $storage, $setupFingerprint = NULL, $onlyExistingFiles = FALSE)
	{
		$this->templateLocator = $templateLocator;
        $cache = new Cache($storage, 'Rixxi.TemplateLocator');
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


	public function formatComponentTemplateFiles(Component $component, $renderMode = ITemplateLocator::DEFAULT_COMPONENT_RENDER_MODE)
	{
		$hash = $component->getPresenter()->getName() . '|' . $component->getReflection()->getShortName() . '|' . $renderMode;
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
