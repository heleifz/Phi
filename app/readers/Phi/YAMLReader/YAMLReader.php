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
		$this->parseFilename($this->path);
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
		$this->metadata["name"] = $fields[3];
		// normalize date format		
		$timestamp = strtotime($fields[0].'/'.$fields[1].'/'.$fields[2]);
		$this->metadata["timestamp"] = $timestamp;
		$longDate = date("Y-m-d", $timestamp);
		$shortDate = date("y-n-j", $timestamp);
		$longFields = explode('-', $longDate);
		$shortFields = explode('-', $shortDate);
		$this->metadata["year"] = $longFields[0];
		$this->metadata["month"] = $longFields[1];
		$this->metadata["day"] = $longFields[2];
		$this->metadata["short_year"] = $shortFields[0];
		$this->metadata["short_month"] = $shortFields[1];
		$this->metadata["short_day"] = $shortFields[2];
		$this->metadata["date"] = $longDate;
		$this->metadata["short_date"] = $shortDate;
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