<?php

namespace Phi;

class VariableResolver {

	public static function resolveString($str, $context) {
		$pattern = '/:([a-zA-Z_]+)/m';	
		$result = preg_replace_callback($pattern, function ($matches) use($context) {
			if (!array_key_exists($matches[1], $context)) {
				return $matches[0];
			}
			return $context[$matches[1]];
		}, $str);
		return $result;
	}

	public static function resolveArray($arr, $context, $exclude_key=array()) {
		// substitude variables
		foreach ($arr as $k => $v) {
			if (!in_array($k, $exclude_key)) {
				// do not substitude content part
				if (is_string($v)) {
					$arr[$k] = static::resolveString($v, $context);
				}
				// recursively resolve variables in nested array
				elseif (is_array($v)) {
					$arr[$k] = static::resolveArray($v, $context, $exclude_key);
				}
			}
		}	
		return $arr;
	}

}