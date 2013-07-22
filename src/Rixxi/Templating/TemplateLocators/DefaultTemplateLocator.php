<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
use Rixxi;


class DefaultTemplateLocator implements Rixxi\Templating\ITemplateLocator
{

	public function formatLayoutTemplateFiles(Presenter $presenter)
	{
		$name = $presenter->getName();
		$_presenter = substr($name, strrpos(':' . $name, ':'));
		$layout = $presenter->layout ? $presenter->layout : 'layout';
		$dir = dirname($presenter->getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		$list = array(
			"$dir/templates/$_presenter/@$layout.latte",
			"$dir/templates/$_presenter.@$layout.latte",
			"$dir/templates/$_presenter/@$layout.phtml",
			"$dir/templates/$_presenter.@$layout.phtml",
		);
		do {
			$list[] = "$dir/templates/@$layout.latte";
			$list[] = "$dir/templates/@$layout.phtml";
			$dir = dirname($dir);
		} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));

		return $list;
	}


	public function formatTemplateFiles(Presenter $presenter)
	{
		$name = $presenter->getName();
		$view = $presenter->view;
		$_presenter = substr($name, strrpos(':' . $name, ':'));
		$dir = dirname($presenter->getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);

		return array(
			"$dir/templates/$_presenter/$view.latte",
			"$dir/templates/$_presenter.$view.latte",
			"$dir/templates/$_presenter/$view.phtml",
			"$dir/templates/$_presenter.$view.phtml",
		);
	}
}
