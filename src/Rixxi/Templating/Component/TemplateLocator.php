<?php

namespace Rixxi\Templating\Component;

use Nette;
use Nette\Templating\FileTemplate;
use Rixxi;
use Rixxi\Templating\InvalidStateException;


trait TemplateLocator
{

	/**
	 * @inject
	 * @var Rixxi\Templating\ITemplateLocator
	 */
	public $templateLocator;


	protected function setTemplateFilename(FileTemplate $template, $view = 'default')
	{
		if (!$this->templateLocator instanceof Rixxi\Templating\ITemplateLocator) {
			throw new InvalidStateException("Component templateLocator must be instance of 'Rixxi\\Templating\\ITemplateLocator'.");
		}

		$files = $this->templateLocator->formatComponentTemplateFiles($this, $view);
		foreach ($files as $file) {
			if (is_file($file)) {
				$template->setFile($file);
				break;
			}
		}

		if (!$template->getFile()) {
			$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\xE2\x80\xA6\$1", reset($files));
			$file = strtr($file, '/', DIRECTORY_SEPARATOR);
			throw new Nette\FileNotFoundException("Component template not found. Missing template '$file'.");
		}
	}

}
