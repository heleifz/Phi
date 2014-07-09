<?php

namespace Phi;

class FrontMatter { 

	public function parse($fullText) {
		$regex = '/^\s*---((?:.|\\n)*?)---((?:.|\\n)*)$/m';
		$matches = array();
		$matched = preg_match($regex, $fullText, $matches);
		$result = array();
		if (!$matched || count($matches) != 3) {
			if (!preg_match('/^\s*---/', $fullText)) {
				$result['metadata'] = array();
				$result['content'] = $fullText;
			} else {
				return NULL;
			}
		} else {
			$result['metadata'] = \spyc_load($matches[1]);
			$result['content'] = $matches[2];
		}	
		return $result;
	}

}