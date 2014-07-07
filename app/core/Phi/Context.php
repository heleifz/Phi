<?php

namespace Phi;

class Context {
	
	private $data = NULL;

	public function __construct($data = array()) {
		$this->data = $data;
	}

	// Parse YAML file and return an array
	public static function fromYML($path) {
		return new Context(\spyc_load_file($path));
	}

	public function toArray() {
		return $this->data;
	}

	public function merge(Context $other, $at="") {
		$fields = $this->breakFields($at);
		$result =& $this->data;
		foreach ($fields as $field) {
			if (!is_array($result))	{
				$result = array();
			}
			if (!array_key_exists($field, $result)) {
				$result[$field] = array();
			}
			$result =& $result[$field];
		}
		$result = \Phi\Utils::arrayMergeRecursiveDistinct($result, $other->toArray());
		return $this;
	}

	public function get($query)
	{
		if (is_string($query)) {
			$fields = $this->breakFields($query);
			$result = $this->data;
			foreach ($fields as $field) {
				// if not exist, return NULL
				if (!is_array($result) || !array_key_exists($field, $result)) {
					return NULL;
				}
				$result = $result[$field];
			}
			return $result;
		} elseif (is_array($query)) {
			// allow query for multiple values
			$result = array();
			foreach ($query as $q) {
				$result[] = $this->get($q);
			}
			return $result;
		}
	}

	public function set($key, $value) {
		$fields = $this->breakFields($key);
		$level = &$this->data;
		foreach ($fields as $field) {
			$level = &$level[$field];
		}
		$level = $value;
	}	

	private function breakFields($query) {
		$fields = explode('.', $query);
		if (count($fields) == 1 && empty($fields[0])) {
			unset($fields[0]);
		}
		return $fields;
	}
}