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


	public function getView($view)
	{
		return $view ?: $this->view;
	}

}