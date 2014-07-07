<?php

class DumpGenerator implements \Phi\Generator {
	public function getName() {
		return "dump";
	}
	public function generate(\Phi\Context $context) {
		var_dump($context->toArray());
	}	
}