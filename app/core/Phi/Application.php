<?php

namespace Phi;

class Application extends \Illuminate\Container\Container {

	private $coreComponents = array(
		'Phi\\ServiceDispatcher',
		'Phi\\ParserDispatcher',
		'Phi\\ErrorHandler',
		'Phi\\FileSystem',
	);

	public function __construct() {
		foreach ($this->coreComponents as $component) {
			$this->instance($component, $this->make($component));
		}
	}

	public function registerService($className) {
		$service = $this->make($className);	
		$this->make('Phi\\ServiceDispatcher')->register($service);
	}

	public function registerParser($className) {
		$parser = $this->make($className);
		$this->make('Phi\\ParserDispatcher')->register($parser);
	}

	public function start($argv) {
		$this->make('Phi\\ServiceDispatcher')->dispatch($argv);	
	}
}