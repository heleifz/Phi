<?php

namespace Phi;

class GeneratorDispatcher {
	private $generatorMap = array();	

	public function register(Generator $generator) {
		$this->generatorMap[$generator->getName()] = $generator;
	}

	public function dispatch($name) {
		if (!array_key_exists($name, $this->generatorMap)) {
			return NULL;
		}
		return $this->generatorMap[$name];
	}
}