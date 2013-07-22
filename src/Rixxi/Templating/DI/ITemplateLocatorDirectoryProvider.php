<?php

namespace Rixxi\Templating\DI;


interface ITemplateLocatorDirectoryProvider
{

	/**
	 * Return list of directories with their priorities (higher is better)
	 * Eg.:
	 * 	'dir' => 1
	 *
	 * @return array
	 */
	function getTemplateLocatorDirectories();

}
