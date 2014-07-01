<?php

namespace Phi\MarkdownParser;

class MarkdownParser extends \ParsedownExtra implements \Phi\Parser {

	public function getExtensions() {
		return array('md', 'markdown', 'mdown');
	}
	
	public function parse($text) {
		return $this->text($text);
	}
}