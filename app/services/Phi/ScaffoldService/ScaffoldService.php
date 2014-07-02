<?php

namespace Phi\ScaffoldService;

class ScaffoldService implements \Phi\Service {

	private $fileSystem;
	private $console;
	private $config;

	public function __construct(\Phi\FileSystem $fileSystem,
								\Phi\Console $console,
								\Phi\Config $config) {
		$this->fileSystem = $fileSystem;
		$this->console = $console;
		$this->config = $config;
		// load default configuration
		$this->config->setPath(__DIR__.'/../../../default.yaml');
	}

	public function getName() {
		return "scaffold";
	}

	public function getDescription() {
		return "Create scaffold for your application.";
	}

	public function execute($arguments, $flags) {
		$path = $arguments[0];
		$this->createDirectory($path);
		$this->fileSystem->copyDirectory(__DIR__ .'/scaffold', $path);
		$this->createRemainingDirectories($path);
	}

	public function getCommandOptions() {
		$path = new \Phi\CommandOption();
		$path->setDescription("Path to the destination.")->setDefault('.')->setRequired()
			 ->setValidator(array($this->fileSystem, 'isValidPath'), "Invalid path.");
		return array($path);
	}

	/**
	 * Create scaffold root directory
	 *
	 * @param string $path
	 */
	private function createDirectory($path) {
		if ($this->fileSystem->exists($path)) {
			if (!$this->console->yesOrNo("Path / file exists, continue ?")) {
				exit(0);
			}
			if ($this->fileSystem->isDirectory($path)) {
				$this->fileSystem->clearDirectory($path);
				return;
			} else {
				$this->fileSystem->delete($path);
			}
		}
		if (!$this->fileSystem->makeDirectory($path, 775, true)) {
			throw new \Exception("Cannot create directory : $path.");
		}
	}

	private function createRemainingDirectories($path) {
		$folders = $this->config->get(array('source', 'destination', 'assets', 'templates'));
		foreach ($folders as $folder) {
			$this->fileSystem->makeDirectory($path.'/'.$folder, 775, true);
		}
	}
}