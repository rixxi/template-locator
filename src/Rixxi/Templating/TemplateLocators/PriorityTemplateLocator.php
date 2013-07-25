<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\Utils\Arrays;
use Rixxi;



class PriorityTemplateLocator implements Rixxi\Templating\ITemplateLocator
{

	/** @var array */
	private $directories;

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
		$directories = $this->getAdjustedDirectories($name, $dir);
		foreach ($directories as $base => $dir) {
			$list[] = $this->getLayoutTemplateFiles($dir, $_presenter, $layout);
			if ($base !== $dir) {
				$list[] = $this->getLayoutTemplateFiles(dirname($dir), $_presenter, $layout);
			}
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
		$directories = $this->getAdjustedDirectories($name, $dir);
		$list = array();
		foreach ($directories as $base => $dir) {
			$list[] = $this->getTemplateFiles($dir, $_presenter, $view);
			if ($base !== $dir) {
				$list[] = $this->getTemplateFiles(dirname($dir), $_presenter, $view);
			}
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


	public function formatComponentTemplateFiles(Component $component)
	{
		$presenter = $component->getPresenter();
		$name = $presenter->getName();
		$_presenter = substr($name, strrpos(':' . $name, ':'));
		$view = isset($component->view) ? $component->view : 'default';

		$componentShortName = $component->getReflection()->getShortName();
		$variants = $this->getComponentVariants($componentShortName, $view);

		$dir = dirname($presenter->getReflection()->getFileName());
		$directories = $this->getAdjustedDirectories($name, $dir);
		foreach ($directories as $base => $dir) {
			$this->appendPrefixed($list, "$dir/templates/$_presenter/components", $variants);
			if ($base !== $dir) {
				$this->appendPrefixed($list, dirname($dir) . "/templates/$_presenter/components", $variants);
			}
		}

		do {
			$parents = array();
			foreach ($directories as $dir) {
				$this->appendPrefixed($list, "$dir/templates/components", $variants);
				$parents[] = dirname($dir);
			}
			$directories = $parents;
		} while ($directories && ($name = substr($name, 0, strrpos($name, ':'))));

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


	private function getComponentVariants($name, $view)
	{
		$list = array();
		if ($view !== 'default') {
			$list[] = "$name/$view.latte";
			$list[] = "$name.$view.latte";
			$list[] = "$name/$view.phtml";
			$list[] = "$name.$view.phtml";
		}

		$list[] = "$name/default.latte";
		$list[] = "$name.default.latte";
		$list[] = "$name/default.phtml";
		$list[] = "$name.default.phtml";

		$list[] = "$name.latte";
		$list[] = "$name.phtml";

		return $list;
	}


	private function getAdjustedDirectories($name, $presenterDir)
	{
		foreach ($this->directories as $dir)
		{
			if (0 === ($pos = strpos($presenterDir, $dir))) {
				if ($presenterDir === $dir) {
					$values = array_values($this->directories);
					$adjusted = array_combine($values, $values);

				} else {
					$adjusted = array();
					$path = substr($presenterDir, strlen($dir) + 1);
					foreach ($this->directories as $dir) {
						$adjusted[$dir] = "$dir/$path";
					}
				}

				return $adjusted;
			}
		}

		throw new \UnexpectedValueException("Presenter directory '$presenterDir' is not amongst directories");
	}
}
