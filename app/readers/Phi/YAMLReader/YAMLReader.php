<?php

namespace Phi\YAMLReader;

class YAMLReader implements \Phi\Reader {

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
		$fields = $this->parseFilename($this->path);
		$this->metadata["year"] = $fields[0];
		$this->metadata["month"] = $fields[1];
		$this->metadata["day"] = $fields[2];
		$this->metadata["name"] = $fields[3];
		$this->metadata["date"] = $fields[0].'/'.$fields[1].'/'.$fields[2];
	}

	public function getMetadata() {
		return $this->metadata;
	}

	public function getBody() {
		return $this->body;
	}

	private function parseFilename($path) {
		$filename = $this->fileSystem->fileName($path);
		$fields = explode('-', $filename, 4);
		if (count($fields) != 4 || !checkdate($fields[1], $fields[2], $fields[0])) {
			throw new \Exception("Error parsing filename : $filename.");
		}
		return $fields;
	}

	private function seperateParts() {
		$regex = '/^\s*---((?:.|\\n)*?)---((?:.|\\n)*)$/m';
		$matches = array();
		$result = preg_match($regex, $this->text, $matches);
		if (!$result || count($matches) != 3) {
			// if article contains no metadata, use default configuration
			if (!preg_match('/^\s*---/', $this->text)) {
				$this->metadata = array();
				$this->body = $this->text;
			} else {
				throw new \Exception("Could not parse the metadata of ".$this->path.'.');
			}
		} else {
			$this->metadata = \spyc_load($matches[1]);
			$this->body = $matches[2];
		}
	}
}