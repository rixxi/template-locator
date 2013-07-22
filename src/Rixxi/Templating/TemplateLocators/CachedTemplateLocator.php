<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
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
	 * @var bool
	 */
	private $onlyExistingFiles;


	/**
	 * @param Rixxi\Templating\ITemplateLocator $templateLocator
	 * @param Nette\Caching\Cache $cache
	 * @param string $setupFingerprint
	 * @param bool $onlyExistingFiles
	 */
	public function __construct(Rixxi\Templating\ITemplateLocator $templateLocator, Nette\Caching\Cache $cache, $setupFingerprint, $onlyExistingFiles = FALSE)
	{
		$this->templateLocator = $templateLocator;
		$this->filesCache = $cache->derive('files');
		if ($setup !== $this->filesCache->load('setup')) {
			$this->filesCache->save('setup', $setupFingerprint);
		}
		$this->layoutFilesCache = $cache->derive('layoutFiles');
		if ($setup !== $this->layoutFilesCache->load('setup')) {
			$this->layoutFilesCache->save('setup', $setupFingerprint);
		}
		$this->onlyExistingFiles = $onlyExistingFiles;
	}


	public function formatLayoutTemplateFiles(Presenter $presenter)
	{
		if (NULL === $this->layoutFilesCache[$name = $presenter->getName()]) {
			$templateLocator = $this->templateLocator;
			$onlyExistingFiles = $this->onlyExistingFiles;
			return $this->layoutFilesCache->save($name, function () use ($presenter, $templateLocator, $onlyExistingFiles) {
				$list = $templateLocator->formatTemplateFiles($presenter);
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

}
