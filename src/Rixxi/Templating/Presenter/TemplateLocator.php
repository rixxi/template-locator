<?php

namespace Rixxi\Templating\Presenter;

use Rixxi;


trait TemplateLocator
{

	/**
	 * @inject
	 * @var Rixxi\Templating\ITemplateLocator
	 */
	public $templateLocator;


	public function formatLayoutTemplateFiles()
	{
		return $this->templateLocator->formatLayoutTemplateFiles($this);
	}


	public function formatTemplateFiles()
	{
		return $this->templateLocator->formatTemplateFiles($this);
	}

}