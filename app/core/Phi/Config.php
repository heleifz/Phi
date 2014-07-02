<?php

namespace Phi;

/**
 * Project configuration reader
 */
class Config {

	private $path = NULL;
	private $config = NULL;

	public function setPath($path) {
		if ($path != $this->path) {
			$this->path = $path;
			$this->config = \spyc_load_file($path);
			var_dump($this->config);
		}
	}

	public function get($query)
	{
		$fields = explode('.', $query);
		$result = $this->config;
		foreach ($fields as $field) {
			if (!is_array($result) || !array_key_exists($field, $result)) {
				return NULL;
			}
			$result = $result[$field];
		}
		return $result;
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