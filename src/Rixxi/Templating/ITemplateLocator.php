<?php

namespace Rixxi\Templating;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;


interface ITemplateLocator
{

	const DEFAULT_COMPONENT_RENDER_MODE = 'default';

	/**
	 * Formats layout template file names.
	 *
	 * @param Presenter
	 * @return array
	 */
	function formatLayoutTemplateFiles(Presenter $presenter);


	/**
	 * Formats view template file names.
	 *
	 * @param Presenter
	 * @return array
	 */
	function formatTemplateFiles(Presenter $presenter);


	/**
	 * Formats component template file names.
	 *
	 * @param Component
	 * @param string
	 * @return array
	 */
	function formatComponentTemplateFiles(Component $component, $renderMode = self::DEFAULT_COMPONENT_RENDER_MODE);

}
