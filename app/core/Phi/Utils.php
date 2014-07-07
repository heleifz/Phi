<?php

namespace Phi;

// static utility class
class Utils {

	public static function camelToLower($input) {
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
		$ret = $matches[0];
		foreach ($ret as &$match) {
			$match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
		}
		return implode('-', $ret);
	}

	public static function arrayMergeRecursiveDistinct(array $array1, array $array2) {
		$merged = $array1;
		$array2 = array_reverse($array2);
		foreach ($array2 as $key => &$value)
		{
			if (is_numeric($key)) {
				array_unshift($merged, $value);
			} elseif (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key] = static::arrayMergeRecursiveDistinct($merged[$key], $value);
			} else {
				$merged[$key] = $value;
			}
		}
		return $merged;
	}

	public static function normalizeUrl($url) {
		$url = strtr($url, '\\', '/');
		$url = preg_replace('#/+#','/',$url);
		$url = preg_replace('#/$#', '', $url);
		if (!preg_match('/^.*\\.[a-zA-Z]+$/i', $url)) {
			$url .= '/index.html';
		}
		// convert to absolute path
		if (!empty($url) && $url[0] != '/') {
			$url = '/'.$url;
		}
		return $url;
	}

	public static function insertVariables($str, $context) {
		$pattern = '/:([a-zA-Z_]+)/m';	
		$result = preg_replace_callback($pattern, function ($matches) use($context) {
			if (!array_key_exists($matches[1], $context)) {
				return $matches[0];
			}
			return $context[$matches[1]];
		}, $str);
		return $result;
	}
}