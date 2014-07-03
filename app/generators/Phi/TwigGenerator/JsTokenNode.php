<?php

namespace Phi\TwigGenerator;

class JsTokenNode extends \Twig_Node {

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
		->write('echo \'<script src="'
			.$prefix.$url.'"></script>\''.'."\n"')->raw(";\n");
	}
}