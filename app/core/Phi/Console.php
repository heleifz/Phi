<?php

namespace Phi;

/**
 * Console Utilities
 */
class Console {

	public function write($text) {
		echo $text;
	}

	public function writeLine($text) {
		$this->write($text . PHP_EOL);
	}

	public function readLine() {
		return fgets(STDIN);
	}

	public function read($length) {
		return fread(STDIN, $length);
	}

	public function yesOrNo($question) {
		echo "$question (Y/N):";
		$input = trim($this->readLine());
		if (strtolower($input) == 'y') {
			return true;
		} else {
			return false;
		}
	}
}