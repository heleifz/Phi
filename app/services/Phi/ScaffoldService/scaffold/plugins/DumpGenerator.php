<?php

class DumpGenerator implements \Phi\Generator {
	public function getName() {
		return "dump";
	}
	public function generate($context) {
		var_dump($context);
	}	
}