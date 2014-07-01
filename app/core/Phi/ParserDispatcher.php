<?php

namespace Phi;

class ParserDispatcher {

	private $parserMap;
	private $fileSystem;
	private $reader;

	public function __construct(\Phi\FileSystem $fileSystem, \Phi\Reader $reader) {
		$this->fileSystem = $fileSystem;
		$this->reader = $reader;
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
		$this->reader->setPath($filepath);
		$body = $parser->parse($this->reader->getBody());
		$data = $this->reader->getMetadata();
		$data['content'] = $body;
		return $data;
	}

	public function getExtensions() {
		return array_keys($this->parserMap);
	}
}
