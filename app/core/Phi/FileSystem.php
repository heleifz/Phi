<?php

namespace Phi;

/**
 * File system utilities
 */
class FileSystem {

	private $finder;

	public function __construct(\Symfony\Component\Finder\Finder $finder) {
		$this->finder = $finder;
	}

	public function exists($path) {
		return file_exists($path);
	}

	public function includeOnce($path) {
		include_once $path;	
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

	public function fileName($path) {
		return pathinfo($path, PATHINFO_FILENAME);	
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

	public function walk($path, $ignoreVCS = true, $ext = array('*'), 
		                 $exclude_path = array(), $exclude_name = array(), $depth=NULL) {
		$this->finder = $this->finder->create();
		foreach ($ext as $extension) {
			$this->finder->name('*.'.$extension);
		}
		foreach ($exclude_path as $p) {
			$this->finder->notPath($p);
		}
		foreach ($exclude_name as $n) {
			$this->finder->notName($n);
		}
		if ($depth) {
			$this->finder->depth($depth);
		}
		$this->finder->files()
		      		 ->ignoreVCS($ignoreVCS)
		       		 ->in($path);
		$result = array_map(function ($file) {
			return array(
				'relative' => $file->getRelativePathname(),
				'filename' => $file->getFileName(),
				'relativeDir' => $file->getRelativePath()
			);
		}, iterator_to_array($this->finder));
		return $result;
	}

	public function writeRecursively($path, $content, $overwrite = true, $mode = 0775) {
		$dirName = $this->directoryName($path);
		$result = true;
		if(!$this->isFile($dirName)) {
    		$result = $this->makeDirectory($dirName, $mode, true) && $result;
    	}
    	return $this->write($path, $content, $overwrite) && $result;
	}

	public function makeDirectory($path, $mode = 0755, $recursive = false)
	{
		return @mkdir($path, $mode, $recursive);
	}

	public function isValidPath($path) {
		$path = trim($path);
		if (preg_match('/^[^*?"<>|:]*$/', $path)) {
			return true;
		}

		if (!defined('WINDOWS_SERVER')) {
			$tmp = dirname(__FILE__);
			if (strpos($tmp, '/', 0) !== false) {
				define('WINDOWS_SERVER', false);
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
		$result = @mkdir($dst);
		while (false !== ($file = readdir($dir))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($src.'/'.$file)) {
					self::copyDirectory($src.'/'.$file, $dst.'/'.$file);
				} else {
					$result = @copy($src.'/'.$file, $dst.'/'.$file) && $result;
				}
			}
		}
		closedir($dir);
		return $result;
	}

	public function deleteDirectory($dir) {
		$dirs = @scandir($dir);
		if ($dirs === false) {
			return false;
		}
		$files = array_diff($dirs, array('.', '..'));
		$result = true;
		foreach ($files as $file) {
			if (is_dir("$dir/$file")) {
				self::deleteDirectory("$dir/$file");
			} else {
				$result = @unlink("$dir/$file") && $result;
			}
		}
		return @rmdir($dir) && $result;;
	}

	public function clearDirectory($dir) {
		$dirs = @scandir($dir);
		if ($dirs === false) {
			return false;
		}
		$files = array_diff($dirs, array('.', '..'));
		$result = true;
		foreach ($files as $file) {
			if (is_dir("$dir/$file")) {
				self::deleteDirectory("$dir/$file");
			} else {
				$result = @unlink("$dir/$file") && $result;
			}
		}
		return $result;
	}
}