<?php

namespace Phi;

class ParserDispatcher {

	private $parserMap;
	private $fileSystem;

	public function __construct(\Phi\FileSystem $fileSystem) {
		$this->fileSystem = $fileSystem;
	}

	public function register(Parser $parser) {
		$extensions = $parser->getExtensions();
		foreach ($extensions as $ext) {
			$this->parserMap[$ext] = $parser;
		}
	}

	public function dispatch($filepath) {
		$ext = $this->fileSystem->getExtension($filepath);
		$parser = $this->parserMap[$ext];
		$content = $this->fileSystem->read($filepath);
		return $parser->parse($content);
	}

	public function getExtensions() {
		return array_keys($this->parserMap);
	}
}
