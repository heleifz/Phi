<?php

namespace Phi\TwigGenerator;

class CssTokenParser extends \Twig_TokenParser {

	private $root;

	public function __construct($root) {
		$this->root = $root;
	}

	public function parse(\Twig_Token $token) {
		$parser = $this->parser;
		$stream = $parser->getStream();
		$url = $stream->expect(\Twig_Token::STRING_TYPE)->getValue();
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		return new CssTokenNode($this->root, $url, $token->getLine(), $this->getTag());
	}

	public function getTag() {
		return 'css';
	}
}