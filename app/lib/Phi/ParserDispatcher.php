<?php

namespace Phi;

class ParserDispatcher {

	private $parserMap;

	public function register(Parser $parser) {
		$extensions = $parser->getExtensions();
		foreach ($extensions as $ext) {
			$this->parserMap[$ext] = $parser;
		}
	}

	public function dispatch($filepath) {
		$ext = \Phi\FileSystem::getFileExtension($filepath);
		$parser = $this->parserMap[$ext];
		$content = file_get_contents($filepath);
		return $parser->parse($content);
	}

	public function getExtensions() {
		return array_keys($this->parserMap);
	}
}
