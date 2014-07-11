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

	public static function excerpt($html, $tag) {
		$pattern = '#<'.$tag.'[^>]*>|</'.$tag.'>#im';
		$result = preg_match($pattern, $html, $matches, PREG_OFFSET_CAPTURE);
		if ($result !== 1) {
			return strip_tags($html);
		}
		$initPos = $matches[0][1];
		$stack = array($initPos);
		$offSet = $initPos + strlen($matches[0][0]);
		while (!empty($stack)) {
			$result = preg_match($pattern, $html, $matches,
							   PREG_OFFSET_CAPTURE, $offSet);
			// fallback
			if ($result !== 1) {
				return strip_tags(substr($html, $initPos));
			}
			$isClose = $matches[0][0][1] == '/';
			if (!$isClose) {
				array_push($stack, $matches[0][1]);
				$offSet = strlen($matches[0][0]) + $matches[0][1];
			} else {
				$pos = array_pop($stack);
				if (empty($stack)) {
					return strip_tags(substr($html, $pos, $matches[0][1] - $pos));
				}
				$offSet = strlen($matches[0][0]) + $matches[0][1];
			}
		}
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

	public static function isInt($num) {
		return ctype_digit($num) || is_int($num);
	}
}