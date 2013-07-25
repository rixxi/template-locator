<?php

namespace Rixxi\Templating;

use Nette\Application\UI\Presenter;


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

}
