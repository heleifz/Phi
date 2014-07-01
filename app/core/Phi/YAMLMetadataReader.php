<?php

namespace Phi;

class YAMLMetadataReader implements Reader {

	private $path = NULL;
	private $text = NULL;
	private $body = NULL;
	private $metadata = NULL;

	private $fileSystem;

	public function __construct(\Phi\FileSystem $fileSystem) {
		$this->fileSystem = $fileSystem;
	}

	public function setPath($path) {
		$this->path = $path;
		$this->text = $this->fileSystem->read($path);
		$this->seperateParts($this->text);
		// compute url if not given
	}

	public function getMetadata() {
		return $this->metadata;
	}

	public function getBody() {
		return $this->body;
	}

	private function seperateParts() {
		$regex = '/^\s*---((?:.|\\n)*?)---((?:.|\\n)*)$/m';
		$matches = array();
		$result = preg_match($regex, $this->text, $matches);
		if (!$result || count($matches) != 3) {
			throw new \Exception("Could not parse the metadata of ".$this->path.'.');
		}
		$this->metadata = \spyc_load($matches[1]);
		$this->body = $matches[2];
	}
}