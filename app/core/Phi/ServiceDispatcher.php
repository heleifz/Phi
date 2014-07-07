<?php

namespace Phi;

class ServiceDispatcher {

	private $serviceMap = array();

	public function dispatch($arguments) {
		if (count($arguments) == 1) {
			echo $this->getHelp();
			exit;
		}
		array_shift($arguments);
		$serviceName = $arguments[0];
		if (!array_key_exists($serviceName, $this->serviceMap)) {
			echo "Illegal command, please try again.".PHP_EOL;
			echo $this->getHelp();
			exit;
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
