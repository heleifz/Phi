<?php

namespace Phi;

class Application extends \Illuminate\Container\Container {
	private $serviceName;

	private $serviceMap = array();
	private $commandMap = array();

	public function __construct() {
	}

	public function registerService($className) {
		$service                               = $this->make($className);
		$this->serviceMap[$service->getName()] = $service;
		$command                               = new Command($service->getCommandOptions());
		$this->commandMap[$service->getName()] = $command;
	}

	public function start($argv) {
		if (count($argv) == 1) {
			echo $this->getServiceHelp();
			exit;
		}
		array_shift($argv);
		$this->serviceName = $argv[0];

		if (!array_key_exists($this->serviceName, $this->serviceMap)) {
			echo "Illegal command, please try again.".PHP_EOL;
			echo $this->getServiceHelp();
			exit;
		}

		$service = $this->serviceMap[$this->serviceName];
		$command = $this->commandMap[$this->serviceName];
		$command->setTokens($argv);
		$service->execute($command->getArguments(), $command->getFlags());
	}

	private function getServiceHelp() {
		$helpString = "Type 'phi.phar command --help' for help".PHP_EOL.PHP_EOL.
		"Available Commands:".PHP_EOL.PHP_EOL;
		foreach ($this->serviceMap as $name => $service) {
			$helpString .= "  $name : " .$service->getDescription().PHP_EOL;
		}
		return $helpString;
	}
}