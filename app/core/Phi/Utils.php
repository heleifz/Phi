<?php

namespace Phi;

class Utils {

	public function arrayMergeRecursiveDistinct(array &$array1, array &$array2) {
		$merged = $array1;
		foreach ($array2 as $key => &$value)
		{
			if (is_numeric($key)) {
				array_unshift($merged, $value);
			} elseif (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
			} else {
				$merged[$key] = $value;
			}
		}
		return $merged;
	}

	public function startsWith($haystack, $needle) {
		return $needle === "" || strpos($haystack, $needle) === 0;
	}
	
	public function endsWith($haystack, $needle) {
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

	public function normalizeUrl($url) {
		$url = strtr($url, '\\', '/');
		$url = preg_replace('#/+#','/',$url);
		if (!preg_match('/^.*\\.html?$/', $url)) {
			$url .= '/index.html';
		}
		// convert to absolute path
		if (!empty($url) && $url[0] != '/') {
			$url = '/'.$url;
		}
		return $url;
	}

	public function insertVariables($str, $context) {
		$pattern = '/:([a-zA-Z_]+)/m';	
		$result = preg_replace_callback($pattern, function ($matches) use($context) {
			if (!array_key_exists($matches[1], $context)) {
				return '';
			}
			return $context[$matches[1]];
		}, $str);
		return $result;
	}
}