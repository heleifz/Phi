<?php

namespace Phi;

class TwigRenderer implements Renderer {

	private $twig = NULL;
	private $context = array();

	public function setTemplatePath($path) {
		$loader = new \Twig_Loader_Filesystem($path);
		$this->twig = new \Twig_Environment($loader, array('autoescape' => false));
	}

	public function setContext($context) {
		$this->context = $context;
	}

	public function render($data, $template) {
		return $this->twig->render($template, array_merge($data, $this->context));
	}

	/**
	 * Custom filters
	 */
	// ......
}