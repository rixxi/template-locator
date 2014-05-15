<?php

namespace Rixxi\Templating\TemplateLocators;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\Utils\Arrays;
use Rixxi\Templating\ITemplateLocator;


/**
 * Deeper directory will be selected even if there is root with higher priority with matching template.
 * Eg.:
 *  directories:
 *      a: 1
 *      b: +9000
 *  templates:
 *      - a/NameModule/templates/components/Name/default.latte [ deep, selected ]
 *      - b/templates/components/Name/default.latte [ shallow ]
 */
class PriorityTemplateLocator implements ITemplateLocator
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
		$directories = $this->getAdjustedDirectories($presenter, $moduleDepth);
		$list = array();
		foreach ($directories as $dir) {
			$list[] = $this->getLayoutTemplateFiles("$dir/presenters", $_presenter, $layout);
			$list[] = $this->getLayoutTemplateFiles($dir, $_presenter, $layout);
		}

		do {
			foreach ($directories as $dir) {
				$list[] = "$dir/presenters/templates/@$layout.latte";
				$list[] = "$dir/presenters/templates/@$layout.phtml";
				$list[] = "$dir/templates/@$layout.latte";
				$list[] = "$dir/templates/@$layout.phtml";
			}
		} while ($moduleDepth-- && $directories = array_map('dirname', $directories));

		return Arrays::flatten($list);
	}


	public function formatTemplateFiles(Presenter $presenter)
	{
		$name = $presenter->getName();
		$view = $presenter->view;
		$_presenter = substr($name, strrpos(':' . $name, ':'));
		$directories = $this->getAdjustedDirectories($presenter);
		$list = array();
		foreach ($directories as $dir) {
			$list[] = $this->getTemplateFiles("$dir/presenters", $_presenter, $view);
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


	/**
	 * @inherit
	 *
	 * Note: Component class name suffix Component is stripped.
	 *
	 * Presenter templates are added per module
	 *  <directory>/[Name[Module]/][presenters/][<Presenter>/]templates/components/<Name>/<view>.(latte|phtml)
	 *  <directory>/[Name[Module]/][presenters/][<Presenter>/]templates/components/<Name>.<view>.(latte|phtml)
	 *  <directory>/[Name[Module]/][presenters/][<Presenter>/]templates/components/<Name>/default.(latte|phtml)
	 *  <directory>/[Name[Module]/][presenters/][<Presenter>/]templates/components/<Name>.default.(latte|phtml)
	 *  <directory>/[Name[Module]/][presenters/][<Presenter>/]templates/components/<Name>.(latte|phtml)
	 *
	 * Last component directory is added
	 *  <component directory>/[templates/]<Name>/<view>.(latte|phtml)
	 *  <component directory>/[templates/]<Name>.<view>.(latte|phtml)
	 *  <component directory>/[templates/]<Name>/default.(latte|phtml)
	 *  <component directory>/[templates/]<Name>.default.(latte|phtml)
	 *  <component directory>/[templates/]<Name>.(latte|phtml)
	 */
	public function formatComponentTemplateFiles(Component $component, $view = 'default')
	{
		$presenter = $component->getPresenter();
		/** @var \Nette\Application\UI\Presenter $presenter */
		$name = $presenter->getName();
		$_presenter = substr($name, strrpos(':' . $name, ':'));

		$componentShortName = $component->getReflection()->getShortName();
		if (substr($componentShortName, -9) === 'Component') {
			$componentShortName = substr($componentShortName, 0, -9);
		}
		$variants = $this->getComponentVariants($componentShortName, $view);

		$directories = $this->getAdjustedDirectories($presenter, $moduleDepth);
		$list = array();
		foreach ($directories as $dir) {
			$this->appendPrefixed($list, "$dir/presenters/templates/$_presenter/components", $variants);
			$this->appendPrefixed($list, "$dir/templates/$_presenter/components", $variants);
			$this->appendPrefixed($list, "$dir/presenters/templates/components", $variants);
			$this->appendPrefixed($list, "$dir/templates/components", $variants);
		}

		do {
			foreach ($directories as $dir) {
				$this->appendPrefixed($list, "$dir/templates/components", $variants);
				if (basename($dir) === 'presenters') {
					$this->appendPrefixed($list, dirname($dir) . '/templates/components', $variants);
				}
			}
		} while ($moduleDepth-- && $directories = array_map('dirname', $directories));

		$dir = dirname($component->getReflection()->getFileName());
		$this->appendPrefixed($list, "$dir/templates", $variants);
		$this->appendPrefixed($list, "$dir", $variants);

		return $list;
	}


	private function appendPrefixed(&$list, $prefix, $values)
	{
		foreach ($values as $value) {
			$list[] = "$prefix/$value";
		}
	}


	private function getComponentVariants($name, $view)
	{
		$list = array();
		$list[] = "$name/$view.latte";
		$list[] = "$name.$view.latte";
		$list[] = "$name/$view.phtml";
		$list[] = "$name.$view.phtml";

		if ($view !== 'default') {
			$list[] = "$name/default.latte";
			$list[] = "$name.default.latte";
			$list[] = "$name/default.phtml";
			$list[] = "$name.default.phtml";
		}

		$list[] = "$name.latte";
		$list[] = "$name.phtml";

		return $list;
	}


	private function getAdjustedDirectories(Presenter $presenter, &$moduleDepth = NULL)
	{
		preg_match('~(?P<modules>([^\\\\]+Module\\\\)*)(?P<presenter>[^\\\\]+)Presenter~i', get_class($presenter), $matches);
		$slugs = preg_split('~(Module\\\\)~i', $matches['modules'], -1, PREG_SPLIT_NO_EMPTY);
		$moduleDepth = count($slugs);

		if ($moduleDepth === 0) {
			return $this->directories;
		}

		$paths = array(
			implode('Module' . DIRECTORY_SEPARATOR, $slugs) . 'Module',
			implode(DIRECTORY_SEPARATOR, $slugs),
		);

		$adjusted = array();
		foreach ($this->directories as $dir)
		{
			foreach ($paths as $path) {
				$adjusted[] = $dir . DIRECTORY_SEPARATOR . $path;
			}
		}

		return $adjusted;
	}
}
