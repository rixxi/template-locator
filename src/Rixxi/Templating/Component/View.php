<?php

namespace Rixxi\Templating\Component;


trait View
{

	/** @var string */
	private $view = 'default';


	public function setView($view)
	{
		if ($view) {
			$this->view = $view;
		}
		return $this;
	}


	public function getView($view = NULL)
	{
		return $view !== NULL ? $view : $this->view;
	}


	public function __call($name, $args)
	{
		if (isset($name[7]) && strpos($name, 'render') === 0) {
			$this->setView(lcfirst(substr($name, 6)));
			return call_user_func_array([$this, 'render'], $args);
		}

		return parent::__call($name, $args);
	}
}
