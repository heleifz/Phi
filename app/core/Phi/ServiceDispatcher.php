<?php

namespace Phi;

class ServiceDispatcher {

	private $serviceMap = array();
	private $console;

	public function __construct(\Phi\Console $console) {
		$this->console = $console;
	}

	public function dispatch($arguments) {
		if (count($arguments) == 1) {
			$this->console->write($this->getHelp());
			return;
		}
		array_shift($arguments);
		$serviceName = $arguments[0];
		if (!array_key_exists($serviceName, $this->serviceMap)) {
			$this->console->write("Illegal command, please try again.".PHP_EOL);
			$this->console->write($this->getHelp());
			return;
		}
		$service = $this->serviceMap[$serviceName];
		$command = new Command($service->getCommandOptions());
		$command->setDescription($service->getDescription());
		$command->setTokens($arguments);
		$service->execute($command->getArguments(), $command->getFlags());	
	}

	public function register(Service $service) {
		$this->serviceMap[$service->getName()] = $service;
	}

	private function getHelp() {
		$helpString = PHP_EOL."Type 'phi.phar command --help' for help".PHP_EOL.PHP_EOL.
			"Available Commands:".PHP_EOL.PHP_EOL;
		foreach ($this->serviceMap as $name => $service) {
			$helpString .= "  $name : " .$service->getDescription().PHP_EOL;
		}
		return $helpString;	
	}
}
