<?php

namespace Phi;

/**
 * Project configuration reader
 */
class Config {

	private $path = NULL;
	private $config = NULL;
	private $util = NULL;

	public function __construct(Utils $util) {
		$this->util = $util;
	}

	public function setPath($path) {
		if ($path != $this->path) {
			$this->path = $path;
			$this->config = \spyc_load_file($path);
		}
	}

	public function mergePath($path) {
		$config = \spyc_load_file($path);
		$this->config = $this->util->arrayMergeRecursiveDistinct($this->config, $config);
	}

	public function toArray() {
		return $this->config;
	}

	public function merge($items) {

	}

	public function get($query)
	{
		if (is_string($query)) {
			$fields = explode('.', $query);
			$result = $this->config;
			foreach ($fields as $field) {
				if (!is_array($result) || !array_key_exists($field, $result)) {
					return NULL;
				}
				$result = $result[$field];
			}
			return $result;
		} elseif (is_array($query)) {
			$result = array();
			foreach ($query as $q) {
				$result[] = $this->get($q);
			}
			return $result;
		}
	}

	public function set($key, $value) {
		if (is_null($level)) {
			throw new \Exception("Cannot set config item $key to $value");
		}
		$fields = explode('.', $key);
		$level = &$this->config;
		foreach ($fields as $field) {
			$level = &$level[$field];
		}
		$level = $value;
	}	
}