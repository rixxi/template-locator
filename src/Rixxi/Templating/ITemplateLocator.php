<?php

namespace Rixxi\Templating;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;


interface ITemplateLocator
{

	/**
	 * Formats layout template file names.
	 *
	 * @param Presenter $presenter
	 * @return array
	 */
	function formatLayoutTemplateFiles(Presenter $presenter);


	/**
	 * Formats view template file names.
	 *
	 * @param Presenter $presenter
	 * @return array
	 */
	function formatTemplateFiles(Presenter $presenter);


	/**
	 * Formats component template file names.
	 *
	 * @param Component $component
	 * @param string $view
	 * @return array
	 */
	function formatComponentTemplateFiles(Component $component, $view = 'default');

}
