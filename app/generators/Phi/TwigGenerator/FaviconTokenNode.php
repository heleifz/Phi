<?php

namespace Phi\TwigGenerator;

class FaviconTokenNode extends \Twig_Node {

	private $root;

	public function __construct($root, $url, $line, $tag = null) {
		$this->root = $root;
		parent::__construct(array(), array('url' => $url), $line, $tag);
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$url = $this->getAttribute('url');
		if (strpos($url, 'http') === 0) {
			$prefix = '';
		} else {
			$prefix = $this->root.'/';
		}
		$compiler
		->addDebugInfo($this)
		->write('echo \'<link rel="shortcut icon" href="'
			.$prefix.$url.'">\''.'."\n"')->raw(";\n");
	}
}