<?php

namespace Phi;

/**
 * File system utilities
 */
class FileSystem {

	public function exists($path) {
		return file_exists($path);
	}

	public function read($path) {
		return file_get_contents($path);	
	}

	public function delete($path) {
		return @unlink($path);
	}

	public function move($src, $dst) {
		return @rename($src, $dst);
	}

	public function copy($src, $dst) {
		return @copy($src, $dst);
	}

	public function isDirectory($path) {
		return is_dir($path);
	}

	public function isFile($path) {
		return is_file($path);
	}

	public function directoryName($path) {
		return dirname($path);
	}

	public function getExtension($path) {
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	public function write($path, $content, $overwrite = true) {
		if (!$overwrite && $this->isFile($path)) {
			return false;
		}
		return @file_put_contents($path, $content);
	}

	public function writeRecursively($path, $content, $overwrite = true, $mode = 0775) {
		$dirName = $this->directoryName($path);
		if(!$this->isFile($dirName)) {
    		$this->makeDirectory($dirName, $mode, true);
    	}
    	return $this->write($path, $content, $overwrite);
	}

	public function makeDirectory($path, $mode = 0755, $recursive = false)
	{
		return @mkdir($path, $mode, $recursive);
	}

	public function isValidPath($path) {
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

	public function copyDirectory($src, $dst) {
		$dir = opendir($src);
		@mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src.'/'.$file)) {
					self::copyDirectory($src.'/'.$file, $dst.'/'.$file);
				} else {
					copy($src.'/'.$file, $dst.'/'.$file);
				}
			}
		}
		closedir($dir);
	}

	public function deleteDirectory($dir) {
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file"))?self::deleteDirectory("$dir/$file"):unlink("$dir/$file");
		}
		return rmdir($dir);
	}

	public function clearDirectory($dir) {
		$files = array_diff(scandir($dir), array('.', '..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file"))?self::deleteDirectory("$dir/$file"):unlink("$dir/$file");
		}
		return;
	}
}