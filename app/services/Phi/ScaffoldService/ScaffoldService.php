<?php

namespace Phi\ScaffoldService;

class ScaffoldService implements \Phi\Service {

	private $fileSystem;

	public function __construct(\Phi\FileSystem $fileSystem) {
		$this->fileSystem = $fileSystem;
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
			$this->yesOrExit("Path / file exists, continue ?");
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
		$folders = array('site', 'assets', 'templates', 'articles');
		foreach ($folders as $folder) {
			$this->fileSystem->makeDirectory($path.'/'.$folder, 775, true);
		}
	}

	private function yesOrExit($question) {
		echo "$question (Y/N):";
		$input = trim(fgets(STDIN));
		if (strtolower($input) == 'y') {
			return;
		} else {
			exit;
		}
	}

}