<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
use Nette\Utils\Arrays;
use Rixxi;



class PriorityTemplateLocator implements Rixxi\Templating\ITemplateLocator
{

	public function __construct($directories)
	{
		$this->directories = $directories;
	}

	public function formatLayoutTemplateFiles(Presenter $presenter)
	{
		$name = $presenter->getName();
		$_presenter = substr($name, strrpos(':' . $name, ':'));
		$layout = $presenter->layout ? $presenter->layout : 'layout';
		$dir = dirname($presenter->getReflection()->getFileName());
		$directories = array_merge($this->directories, array($dir));
		foreach ($directories as $dir) {
			$list[] = $this->getLayoutTemplateFiles("$dir/templates", $_presenter, $layout);
			$list[] = $this->getLayoutTemplateFiles($dir, $_presenter, $layout);
		}

		do {
			$parents = array();
			foreach ($directories as $dir) {
				$list[] = "$dir/templates/@$layout.latte";
				$list[] = "$dir/templates/@$layout.phtml";
				$parents[] = dirname($dir);
			}
			$directories = $parents;
		} while ($directories && ($name = substr($name, 0, strrpos($name, ':'))));

		return Arrays::flatten($list);
	}


	public function formatTemplateFiles(Presenter $presenter)
	{
		$name = $presenter->getName();
		$view = $presenter->view;
		$_presenter = substr($name, strrpos(':' . $name, ':'));
		$dir = dirname($presenter->getReflection()->getFileName());
		$directories = array_merge($this->directories, array($dir));
		$list = array();
		foreach ($directories as $dir) {
			$list[] = $this->getTemplateFiles("$dir/templates", $_presenter, $view);
			$list[] = $this->getTemplateFiles($dir, $_presenter, $view);
		}

		return Arrays::flatten($list);
	}


	protected function getLayoutTemplateFiles($dir, $presenter, $layout)
	{
		return array(
			"$dir/templates/$presenter/@$layout.latte",
			"$dir/templates/$presenter.@$layout.latte",
			"$dir/templates/$presenter/@$layout.phtml",
			"$dir/templates/$presenter.@$layout.phtml",
		);
	}


	protected function getTemplateFiles($dir, $presenter, $view)
	{
		return array(
			"$dir/templates/$presenter/$view.latte",
			"$dir/templates/$presenter.$view.latte",
			"$dir/templates/$presenter/$view.phtml",
			"$dir/templates/$presenter.$view.phtml",
		);
	}
}
