<?php

namespace Phi;

/**
 * File system utilities
 */
class FileSystem {

	public static function isValidPath($path) {
		$path = trim($path);
		if (preg_match('/^[^*?"<>|:]*$/', $path)) {return true;
		}

		if (!defined('WINDOWS_SERVER')) {
			$tmp = dirname(__FILE__);
			if (strpos($tmp, '/', 0) !== false) {define('WINDOWS_SERVER', false);
			} else {
				define('WINDOWS_SERVER', true);
			}
		}
		if (WINDOWS_SERVER) {
			// check if it's something like C:\
			if (strpos($path, ":") == 1 && preg_match('/[a-zA-Z]/', $path[0])) {
				$tmp  = substr($path, 2);
				$bool = preg_match('/^[^*?"<>|:]*$/', $tmp);
				return ($bool == 1);// so that it will return only true and false
			}
			return false;
		}
		return false;
	}

	public static function copyDirectoryRecursively($src, $dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src.'/'.$file)) {
					self::copyDirectoryRecursively($src.'/'.$file, $dst.'/'.$file);
				} else {
					copy($src.'/'.$file, $dst.'/'.$file);
				}
			}
		}
		closedir($dir);
	}

	public static function deleteDirectory($dir) {
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file"))?self::deleteDirectory("$dir/$file"):unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	public static function deleteDirectoryContents($dir) {
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file"))?self::deleteDirectory("$dir/$file"):unlink("$dir/$file");
		}
		return;
	}

	public static function getFileExtension($path) {
		return pathinfo($path, PATHINFO_EXTENSION);
	}

}