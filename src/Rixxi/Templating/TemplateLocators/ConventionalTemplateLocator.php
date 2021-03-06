<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Rixxi\Templating\ITemplateLocator;


class ConventionalTemplateLocator implements ITemplateLocator
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


	public function formatComponentTemplateFiles(Component $component, $renderMode = ITemplateLocator::DEFAULT_COMPONENT_RENDER_MODE)
	{
		$presenter = $component->getPresenter();
		$name = $presenter->getName();
		$_presenter = substr($name, strrpos(':' . $name, ':'));

		$componentShortName = $component->getReflection()->getShortName();
		$variants = $this->getComponentVariants($componentShortName, $renderMode);

		$dir = dirname($presenter->getReflection()->getFileName());
		$dir = is_dir("$dir/templates") ? $dir : dirname($dir);
		$this->appendPrefixed($list, "$dir/templates/$_presenter/components", $variants);

		do {
			$this->appendPrefixed($list, "$dir/templates/components", $variants);
			$dir = dirname($dir);
		} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));

		$dir = dirname($component->getReflection()->getFileName());
		$this->appendPrefixed($list, "$dir/templates", $variants);
		$this->appendPrefixed($list, "$dir", $variants);

		return $list;
	}


	private function appendPrefixed(&$list, $values, $prefix)
	{
		foreach ($values as $value) {
			$list[] = "$prefix/$value";
		}
	}


	private function getComponentVariants($name, $renderMode)
	{
		$list = array();
		$list[] = "$name/$renderMode.latte";
		$list[] = "$name.$renderMode.latte";
		$list[] = "$name/$renderMode.phtml";
		$list[] = "$name.$renderMode.phtml";

		if ($renderMode !== 'default') {
			$list[] = "$name/default.latte";
			$list[] = "$name.default.latte";
			$list[] = "$name/default.phtml";
			$list[] = "$name.default.phtml";
		}

		$list[] = "$name.latte";
		$list[] = "$name.phtml";

		return $list;
	}

}
