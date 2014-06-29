<?php

namespace Phi\GenerateService;

class GenerateService implements \Phi\Service {

	public function getName() {
		return "generate";
	}

	public function getDescription() {
		return "Generate static site.";
	}

	public function execute($arguments, $flags) {
		echo "hello from generator!";
	}

	public function getCommandOptions() {
		$path = new \Phi\CommandOption();
		$path->setDescription("Path to the Phi application directory.");
		$path->setDefault('.');
		$path->setValidator('is_dir', "Invalid Phi application path");
		return array($path);
	}
}