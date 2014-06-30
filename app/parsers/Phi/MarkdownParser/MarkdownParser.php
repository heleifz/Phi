<?php

namespace Phi\MarkdownParser;

class MarkdownParser extends \ParsedownExtra implements \Phi\Parser {

	public function getExtensions() {
		return array('md', 'markdown', 'mdown');
	}

	public function parse($text) {
		list($meta, $body) = $this->cutOutMetadata($text);
		$result = array();
		$result['meta'] = $this->parseMetadata($meta);
		$result['body'] = $this->text($body);
		return $result;
	}

	protected function cutOutMetadata($text) {
		return array('', $text);
	}

	protected function parseMetadata($meta) {
		return array();
	}
}