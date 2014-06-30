<?php

namespace Phi\ScaffoldService;

class ScaffoldService implements \Phi\Service {

	public function getName() {
		return "scaffold";
	}

	public function getDescription() {
		return "Create scaffold for your application.";
	}

	public function execute($arguments, $flags) {
		$path = $arguments[0];
		$this->createDirectory($path);
		@\Phi\FileSystem::copyDirectoryRecursively(__DIR__ .'/scaffold', $path);
		$this->createRemainingDirectories($path);
	}

	public function getCommandOptions() {
		$path = new \Phi\CommandOption();
		$path->setDescription("Path to the destination.")->setDefault('.')->setRequired()
			 ->setValidator(array('\\Phi\\FileSystem', 'isValidPath'), "Invalid path.");
		return array($path);
	}

	/**
	 * Create scaffold root directory
	 *
	 * @param string $path
	 */
	private function createDirectory($path) {
		if (file_exists($path)) {
			$this->yesOrExit("Path / file exists, continue ?");
			if (is_dir($path)) {
				\Phi\FileSystem::deleteDirectoryContents($path);
				return;
			} else {
				unlink($path);
			}
		}
		if (!@mkdir($path, 775, true)) {
			throw new \Exception("Cannot create directory : $path.");
		}
	}

	private function createRemainingDirectories($path) {
		$folders = array('site', 'assets', 'templates', 'articles');
		foreach ($folders as $folder) {
			@mkdir($path.'/'.$folder, 775, true);
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