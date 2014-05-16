<?php

namespace Rixxi\Application\UI\Control;

use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;
use Nette\FileNotFoundException;
use Rixxi\Templating\ITemplateLocator;


/**
 * ITemplateFactory wrapper for components (control descendants that are not presenters).
 */
class TemplateFactory implements ITemplateFactory
{

	/** @var ITemplateLocator */
	private $templateLocator;

	/** @var ITemplateFactory */
	private $templateFactory;


	public function __construct(ITemplateLocator $templateLocator, ITemplateFactory $templateFactory)
	{
		$this->templateLocator = $templateLocator;
		$this->templateFactory = $templateFactory;
	}


	public function createTemplate(Control $control)
	{
		$template = $this->templateFactory->createTemplate($control);
		$this->setTemplateFile($template, $control);
		if (property_exists($control, 'onRenderModeChange')) {
			$control->onRenderModeChange[] = function (Control $control) {
				$this->setTemplateFile($control->getTemplate(), $control);
			};
		}
		return $template;
	}


	public function setTemplateFile(ITemplate $template, Control $control)
	{
		$renderMode = $control instanceof IRenderMode ? $control->getRenderMode() : IRenderMode::DEFAULT_RENDER_MODE;
		$renderMode = $renderMode === IRenderMode::DEFAULT_RENDER_MODE ? ITemplateLocator::DEFAULT_COMPONENT_RENDER_MODE : $renderMode;
		$files = $this->templateLocator->formatComponentTemplateFiles($control, $renderMode);

		foreach ($files as $file) {
			if (is_file($file)) {
				$template->setFile($file);
				break;
			}
		}

		if (!$template->getFile()) {
			$file = preg_replace('#^.*([/\\\\].{1,70})\z#U', "\xE2\x80\xA6\$1", reset($files));
			$file = strtr($file, '/', DIRECTORY_SEPARATOR);
			throw new FileNotFoundException("Control template not found. Missing template '$file'.");
		}
	}

}
