<?php

namespace Phi;

class Application extends \Illuminate\Container\Container {

	public function __construct() {
		$this->instance('Phi\\Application', $this);	
		$this->instance('Phi\\ServiceDispatcher', new ServiceDispatcher);
		$this->instance('Phi\\ParserDispatcher', new ParserDispatcher);
		$this->instance('Phi\\ErrorHandler', new \Phi\ErrorHandler);
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