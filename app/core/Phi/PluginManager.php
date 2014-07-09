<?php

namespace Phi;

class PluginManager {

	private $app;
	private $fileSystem;

	public function __construct(Application $app, FileSystem $fileSystem) {
		$this->app = $app;
		$this->fileSystem = $fileSystem;
	}

	public function registerDirectory($path) {
		$files = $this->fileSystem->walk($path, true,
			array('php'), array(), array(), '< 1'); 
		foreach ($files as $pathinfo) {
			$this->fileSystem->includeOnce($pathinfo['absolute']);
			$className = $this->fileSystem->fileName($pathinfo['absolute']);
			$this->register($className);
		}
	}

	public function register($className) {
		if (preg_match('/.*(?:p|P)arser$/', $className)) {
			$this->app->registerParser($className);
		} elseif (preg_match('/.*(?:g|G)enerator$/', $className)) {
			$this->app->registerGenerator($className);
		} else {
			throw new \Exception("Unknown plugin : $className");
		}
	}
}