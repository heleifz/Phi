<?php

namespace Phi;

class GeneratorDispatcher {
	private $generatorMap = array();	

	public function register(Generator $generator) {
		$this->generatorMap[$generator->getName()] = $generator;
	}

	public function dispatch($name) {
		if (!array_key_exists($name, $this->generatorMap)) {
			throw new Exception("Unable to find generator : $name");
		}
		return $this->generatorMap[$name];
	}
}