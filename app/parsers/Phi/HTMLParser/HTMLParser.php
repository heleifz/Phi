<?php

namespace Phi\HTMLParser;

class HTMLParser implements \Phi\Parser {
	public function getExtensions() {
		return array('html', 'htm');
	}

	public function parse($text) {
		// return the text as is
		return $text;
	}
}